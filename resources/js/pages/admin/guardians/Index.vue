<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import ResetPasswordDialog from '@/components/admin/dialogs/ResetPasswordDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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

const page = usePage();

const breadcrumbs = [{ title: 'Guardians', href: '/admin/guardians' }];
const baseUrl = '/admin/guardians';

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'phone', label: 'Phone', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Created', sortable: true },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ? props.filters.status : 'all');
const confirmOpen = ref(false);
const confirmTitle = ref('');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(true);
const pendingAction = ref(null);
const resetPasswordOpen = ref(false);
const resetPasswordUser = ref(null);
let searchDebounceTimer = null;

const formErrors = computed(() => page.props.errors ?? {});

watch(
    () => props.filters.search,
    (value) => {
        const normalized = value ?? '';

        if (normalized !== search.value) {
            search.value = normalized;
        }
    },
);

watch(
    () => props.filters.status,
    (value) => {
        const normalized = value ? value : 'all';

        if (normalized !== statusFilter.value) {
            statusFilter.value = normalized;
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

watch(statusFilter, (value) => {
    const currentStatus = props.filters.status ? props.filters.status : 'all';

    if (currentStatus === value) {
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
        baseUrl,
        {
            trash: props.filters.trash ? 1 : 0,
            search: search.value,
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
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

function openConfirm(action, row = null) {
    pendingAction.value = { action, row };
    confirmTitle.value = 'Confirm Action';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;

    if (action === 'delete') {
        confirmTitle.value = 'Delete Guardian';
        confirmDescription.value = 'This will move the guardian to recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Guardian';
        confirmDescription.value = 'This action is irreversible and removes the guardian permanently.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value = 'This will permanently delete all trashed guardians.';
        confirmLabel.value = 'Empty Recycle Bin';
        confirmDestructive.value = true;
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Guardian';
        confirmDescription.value = 'This will restore the guardian from recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'restore-all') {
        confirmTitle.value = 'Restore All Guardians';
        confirmDescription.value = 'This will restore all trashed guardians.';
        confirmLabel.value = 'Restore All';
    }

    if (action === 'suspend') {
        confirmTitle.value = 'Suspend Guardian';
        confirmDescription.value = 'Suspend user will prevent login and dashboard access.';
        confirmLabel.value = 'Suspend';
        confirmDestructive.value = true;
    }

    if (action === 'unsuspend') {
        confirmTitle.value = 'Unsuspend Guardian';
        confirmDescription.value = 'Unsuspend will re-enable access.';
        confirmLabel.value = 'Unsuspend';
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

    if (action === 'delete' && row) {
        router.delete(`/admin/guardians/${row.id}`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/guardians/${row.id}/force`);
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/guardians/${row.id}/restore`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/guardians/recycle-bin/empty');
    }

    if (action === 'restore-all') {
        router.patch('/admin/guardians/recycle-bin/restore-all');
    }

    if (action === 'suspend' && row) {
        router.patch(`/admin/guardians/${row.id}/status`, { status: 'suspended' });
    }

    if (action === 'unsuspend' && row) {
        router.patch(`/admin/guardians/${row.id}/status`, { status: 'active' });
    }

    confirmOpen.value = false;
    resetConfirmState();
}

function actionItemsForRow(row) {
    if (props.filters.trash) {
        return [
            { key: 'restore', label: 'Restore' },
            { key: 'force-delete', label: 'Permanently Delete', destructive: true },
        ];
    }

    return [
        { key: 'view', label: 'View' },
        { key: 'edit', label: 'Edit' },
        {
            key: row.status === 'active' ? 'suspend' : 'unsuspend',
            label: row.status === 'active' ? 'Suspend' : 'Unsuspend',
        },
        { key: 'reset-password', label: 'Reset Password' },
        { key: 'impersonate', label: 'Impersonate', show: canImpersonateRow(row) },
        { key: 'delete', label: 'Delete', destructive: true },
    ];
}

function canImpersonateRow(row) {
    const currentUserId = page.props.auth?.user?.id;
    const isImpersonating = Boolean(page.props.auth?.impersonation?.is_impersonating);

    return !isImpersonating && row.id !== currentUserId && row.status === 'active';
}

function handleRowAction(actionKey, row) {
    if (actionKey === 'view') {
        router.visit(`/admin/guardians/${row.id}`);
        return;
    }

    if (actionKey === 'edit') {
        router.visit(`/admin/guardians/${row.id}/edit`);
        return;
    }

    if (actionKey === 'reset-password') {
        resetPasswordUser.value = row;
        resetPasswordOpen.value = true;
        return;
    }

    if (actionKey === 'impersonate') {
        router.post(`/admin/impersonation/${row.id}`);
        return;
    }

    if (
        actionKey === 'restore' ||
        actionKey === 'delete' ||
        actionKey === 'force-delete' ||
        actionKey === 'suspend' ||
        actionKey === 'unsuspend'
    ) {
        openConfirm(actionKey, row);
    }
}

function submitResetPassword(payload) {
    if (!resetPasswordUser.value) {
        return;
    }

    router.put(`/admin/guardians/${resetPasswordUser.value.id}/password`, payload, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            resetPasswordOpen.value = false;
            resetPasswordUser.value = null;
        },
    });
}

function closeResetPasswordDialog() {
    resetPasswordOpen.value = false;
    resetPasswordUser.value = null;
}
</script>

<template>
    <Head title="Manage Guardians" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold">
                    {{ filters.trash ? 'Guardian Recycle Bin' : 'Guardians' }}
                </h1>

                <div class="flex items-center gap-2">
                    <Link
                        :href="filters.trash ? '/admin/guardians' : '/admin/guardians?trash=1'"
                        class="rounded-md border px-4 py-2 text-sm"
                    >
                        {{ filters.trash ? 'Back to Active' : 'Recycle Bin' }}
                    </Link>

                    <Button
                        v-if="filters.trash"
                        type="button"
                        variant="outline"
                        @click="openConfirm('restore-all')"
                    >
                        Restore All
                    </Button>

                    <Button
                        v-if="filters.trash"
                        type="button"
                        variant="destructive"
                        @click="openConfirm('empty-recycle-bin')"
                    >
                        Empty Recycle Bin
                    </Button>
                </div>
            </div>

            <div class="grid gap-3 rounded-xl border bg-white p-4 md:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="guardian-search">Search</Label>
                    <Input
                        id="guardian-search"
                        v-model="search"
                        type="text"
                        placeholder="Search by name, phone, or email"
                    />
                </div>

                <div class="grid gap-2">
                    <Label>Status</Label>
                    <Select v-model="statusFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All status</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="suspended">Suspended</SelectItem>
                            <SelectItem value="pending_verification">Pending</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                :sort-by="filters.sort"
                :sort-direction="filters.direction"
                empty-text="No guardians found."
                @sort="handleSort"
            >
                <template #cell-created_at="{ value }">
                    {{ value ? new Date(value).toLocaleString() : '—' }}
                </template>

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

        <ResetPasswordDialog
            v-model:open="resetPasswordOpen"
            :user-name="resetPasswordUser?.name"
            :errors="formErrors"
            @submit="submitResetPassword"
            @cancel="closeResetPasswordDialog"
        />
    </AdminLayout>
</template>
