<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
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
});

const breadcrumbs = [{ title: 'Jobs', href: '/admin/tuition/jobs' }];
const baseUrl = '/admin/tuition/jobs';

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'guardian_name', label: 'Guardian' },
    { key: 'category_name', label: 'Category' },
    { key: 'class_name', label: 'Class' },
    { key: 'city_name', label: 'City' },
    { key: 'status', label: 'Status' },
    { key: 'published_at', label: 'Published At' },
    { key: 'expires_at', label: 'Expires At' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
const guardianFilter = ref(props.filters.guardian_id ? String(props.filters.guardian_id) : 'all');
const confirmOpen = ref(false);
const confirmTitle = ref('Confirm Action');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(false);
const pendingAction = ref(null);
let searchDebounceTimer = null;

watch(() => props.filters.q, (value) => {
    const normalized = value ?? '';

    if (search.value !== normalized) {
        search.value = normalized;
    }
});

watch(() => props.filters.status, (value) => {
    const normalized = value || 'all';

    if (statusFilter.value !== normalized) {
        statusFilter.value = normalized;
    }
});

watch(() => props.filters.guardian_id, (value) => {
    const normalized = value ? String(value) : 'all';

    if (guardianFilter.value !== normalized) {
        guardianFilter.value = normalized;
    }
});

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

watch(guardianFilter, (value) => {
    applyFilters({ guardian_id: value === 'all' ? '' : value, page: 1 });
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
            trash: props.filters.trash ? 1 : 0,
            q: search.value,
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
            guardian_id: guardianFilter.value === 'all' ? '' : guardianFilter.value,
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function actionItemsForRow(row) {
    if (props.filters.trash) {
        return [
            { key: 'restore', label: 'Restore' },
            { key: 'force-delete', label: 'Permanently Delete', destructive: true },
        ];
    }

    const actions = [
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete', destructive: true },
    ];

    if (row.status === 'pending') {
        actions.unshift({ key: 'approve', label: 'Approve (Go Live)' });
        actions.unshift({ key: 'cancel', label: 'Cancel' });
    }

    if (row.status === 'live') {
        actions.unshift({ key: 'close', label: 'Close' });
        actions.unshift({ key: 'cancel', label: 'Cancel' });
    }

    if (row.status === 'confirmed') {
        actions.unshift({ key: 'close', label: 'Close' });
    }

    return actions;
}

function handleRowAction(actionKey, row) {
    if (actionKey === 'edit') {
        router.visit(`/admin/tuition/jobs/${row.id}/edit`);

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
        confirmDescription.value = 'This will make the job live.';
        confirmLabel.value = 'Approve';
    }

    if (action === 'cancel') {
        confirmTitle.value = 'Cancel Job';
        confirmDescription.value = 'This will mark the job as cancelled.';
        confirmLabel.value = 'Cancel Job';
    }

    if (action === 'close') {
        confirmTitle.value = 'Close Job';
        confirmDescription.value = 'This will mark the job as closed.';
        confirmLabel.value = 'Close Job';
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Job';
        confirmDescription.value = 'This will restore the job from recycle bin.';
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
        confirmDescription.value = 'This will permanently delete all trashed jobs.';
        confirmLabel.value = 'Empty Recycle Bin';
        confirmDestructive.value = true;
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
        router.patch(`/admin/tuition/jobs/${row.id}/approve`);
    }

    if (action === 'cancel' && row) {
        router.patch(`/admin/tuition/jobs/${row.id}/cancel`, {
            reason: 'Cancelled by admin from listing.',
        });
    }

    if (action === 'close' && row) {
        router.patch(`/admin/tuition/jobs/${row.id}/close`);
    }

    if (action === 'delete' && row) {
        router.delete(`/admin/tuition/jobs/${row.id}`);
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/tuition/jobs/${row.id}/restore`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/tuition/jobs/${row.id}/force`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/tuition/jobs/recycle-bin/empty');
    }

    confirmOpen.value = false;
    resetConfirmState();
}
</script>

<template>
    <Head title="Jobs" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold">{{ filters.trash ? 'Job Recycle Bin' : 'Tuition Jobs' }}</h1>
                    <p class="text-sm text-muted-foreground">
                        Active: {{ counts.active ?? 0 }} | Pending: {{ counts.pending ?? 0 }} | Live: {{ counts.live ?? 0 }} | Trash: {{ counts.trash ?? 0 }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link :href="filters.trash ? baseUrl : `${baseUrl}?trash=1`" class="rounded-md border px-4 py-2 text-sm">
                        {{ filters.trash ? 'Back to Active' : 'Recycle Bin' }}
                    </Link>

                    <Button v-if="filters.trash" type="button" variant="destructive" @click="openConfirm('empty-recycle-bin')">
                        Empty Recycle Bin
                    </Button>

                    <Link v-if="!filters.trash" href="/admin/tuition/jobs/create" class="rounded-md bg-black px-4 py-2 text-sm text-white">
                        Create Job
                    </Link>
                </div>
            </div>

            <div class="grid gap-3 rounded-xl border bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
                <Input v-model="search" type="text" placeholder="Search jobs" class="sm:col-span-2" />

                <Select v-model="statusFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All statuses" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All statuses</SelectItem>
                        <SelectItem v-for="status in statusOptions" :key="status.value" :value="status.value">
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
                        <SelectItem v-for="guardian in guardianOptions" :key="guardian.id" :value="String(guardian.id)">
                            {{ guardian.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DataTable :items="items" :columns="columns" empty-text="No jobs found.">
                <template #cell-status="{ row }">
                    <Badge :variant="row.status === 'live' ? 'default' : 'secondary'">{{ row.status }}</Badge>
                </template>

                <template #cell-published_at="{ value }">{{ value ? new Date(value).toLocaleString() : '—' }}</template>
                <template #cell-expires_at="{ value }">{{ value ? new Date(value).toLocaleString() : '—' }}</template>

                <template #cell-actions="{ row }">
                    <RowActionsDropdown :actions="actionItemsForRow(row)" @select="(action) => handleRowAction(action, row)" />
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
