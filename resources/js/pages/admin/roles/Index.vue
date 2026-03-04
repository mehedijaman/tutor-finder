<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
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

const breadcrumbs = [{ title: 'Roles', href: '/admin/roles' }];
const baseUrl = '/admin/roles';

const columns = [
    { key: 'name', label: 'Role', sortable: true },
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
        confirmTitle.value = 'Delete Role';
        confirmDescription.value = 'This will move the role to recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Role';
        confirmDescription.value =
            'This action is irreversible and removes the role permanently.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'This will permanently delete all trashed roles.';
        confirmLabel.value = 'Empty Recycle Bin';
        confirmDestructive.value = true;
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Role';
        confirmDescription.value =
            'This will restore the role from recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'restore-all') {
        confirmTitle.value = 'Restore All Roles';
        confirmDescription.value = 'This will restore all trashed roles.';
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
        router.delete(`/admin/roles/${row.id}`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/roles/${row.id}/force`);
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/roles/${row.id}/restore`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/roles/recycle-bin/empty');
    }

    if (action === 'restore-all') {
        router.patch('/admin/roles/recycle-bin/restore-all');
    }

    confirmOpen.value = false;
    resetConfirmState();
}

function actionItemsForRow() {
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
        { key: 'delete', label: 'Delete', destructive: true },
    ];
}

function handleRowAction(actionKey, row) {
    if (actionKey === 'edit') {
        router.visit(`/admin/roles/${row.id}/edit`);
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
    <Head title="Manage Roles" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{
                            filters.trash
                                ? 'Role Recycle Bin'
                                : 'Role Management'
                        }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            :href="
                                filters.trash
                                    ? '/admin/roles'
                                    : '/admin/roles?trash=1'
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
                            href="/admin/roles/create"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            Create Role
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
                    placeholder="Search by role name"
                    class="max-w-md"
                />
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                :sort-by="filters.sort"
                :sort-direction="filters.direction"
                empty-text="No roles found."
                @sort="handleSort"
            >
                <template #cell-permissions="{ row }">
                    {{ row.permissions?.join(', ') || '—' }}
                </template>

                <template #cell-created_at="{ value }">
                    {{ value ? new Date(value).toLocaleString() : '—' }}
                </template>

                <template #cell-actions="{ row }">
                    <RowActionsDropdown
                        :actions="actionItemsForRow()"
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
