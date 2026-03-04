<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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

const breadcrumbs = [{ title: 'Admin Users', href: '/admin/users' }];
const baseUrl = '/admin/users';

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'roles', label: 'Roles' },
    { key: 'permissions', label: 'Permissions' },
    { key: 'created_at', label: 'Created', sortable: true },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.search ?? '');
const confirmOpen = ref(false);
const confirmTitle = ref('');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(true);
const pendingAction = ref(null);
let searchDebounceTimer = null;

watch(
    () => props.filters.search,
    (value) => {
        const normalized = value ?? '';

        if (normalized !== search.value) {
            search.value = normalized;
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
            sort: props.filters.sort ?? 'name',
            direction: props.filters.direction ?? 'asc',
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
        confirmTitle.value = 'Delete Admin User';
        confirmDescription.value =
            'This will move the admin user to recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Admin User';
        confirmDescription.value =
            'This action is irreversible and removes the user permanently.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'This will permanently delete all trashed admin users.';
        confirmLabel.value = 'Empty Recycle Bin';
        confirmDestructive.value = true;
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Admin User';
        confirmDescription.value =
            'This will restore the admin user from recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'restore-all') {
        confirmTitle.value = 'Restore All Admin Users';
        confirmDescription.value = 'This will restore all trashed admin users.';
        confirmLabel.value = 'Restore All';
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
        router.delete(`/admin/users/${row.id}`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/users/${row.id}/force`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/users/recycle-bin/empty');
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/users/${row.id}/restore`);
    }

    if (action === 'restore-all') {
        router.patch('/admin/users/recycle-bin/restore-all');
    }

    confirmOpen.value = false;
    resetConfirmState();
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

    return [
        { key: 'edit', label: 'Edit' },
        {
            key: 'impersonate',
            label: 'Impersonate',
            show: canImpersonateRow(row),
        },
        { key: 'delete', label: 'Delete', destructive: true },
    ];
}

function canImpersonateRow(row) {
    const currentUserId = page.props.auth?.user?.id;
    const isImpersonating = Boolean(
        page.props.auth?.impersonation?.is_impersonating,
    );

    return (
        !isImpersonating && row.id !== currentUserId && row.status === 'active'
    );
}

function handleRowAction(actionKey, row) {
    if (actionKey === 'edit') {
        router.visit(`/admin/users/${row.id}/edit`);
        return;
    }

    if (actionKey === 'impersonate') {
        router.post(`/admin/impersonation/${row.id}`);
        return;
    }

    if (
        actionKey === 'delete' ||
        actionKey === 'force-delete' ||
        actionKey === 'restore'
    ) {
        openConfirm(actionKey, row);
    }
}
</script>

<template>
    <Head title="Admin Users" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{
                            filters.trash
                                ? 'Admin Users Recycle Bin'
                                : 'Admin Users'
                        }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            :href="
                                filters.trash
                                    ? '/admin/users'
                                    : '/admin/users?trash=1'
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

                        <Link
                            v-if="!filters.trash"
                            href="/admin/users/create"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            Create Admin User
                        </Link>
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
            >
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search by name or email"
                    class="max-w-md"
                />
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                :sort-by="filters.sort"
                :sort-direction="filters.direction"
                empty-text="No admin users found."
                @sort="handleSort"
            >
                <template #cell-roles="{ row }">
                    {{ row.roles?.join(', ') || '—' }}
                </template>

                <template #cell-permissions="{ row }">
                    {{ row.permissions?.join(', ') || '—' }}
                </template>

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
    </AdminLayout>
</template>
