<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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
    { key: 'name', label: 'Identity', sortable: true },
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
const pendingAction = ref<{ action: string; row: any } | null>(null);
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

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

function handleSort(columnKey: string) {
    const nextDirection =
        props.filters.sort === columnKey && props.filters.direction === 'asc'
            ? 'desc'
            : 'asc';

    applyFilters({ sort: columnKey, direction: nextDirection, page: 1 });
}

function openConfirm(action: string, row: any = null) {
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
        router.delete(`${baseUrl}/${row.id}`, { preserveScroll: true });
    }

    if (action === 'force-delete' && row) {
        router.delete(`${baseUrl}/${row.id}/force-delete`, {
            preserveScroll: true,
        });
    }

    if (action === 'empty-recycle-bin') {
        router.delete(`${baseUrl}/empty-recycle-bin`, {
            preserveScroll: true,
        });
    }

    if (action === 'restore' && row) {
        router.patch(`${baseUrl}/${row.id}/restore`, {}, { preserveScroll: true });
    }

    if (action === 'restore-all') {
        router.patch(`${baseUrl}/restore-all`, {}, { preserveScroll: true });
    }

    resetConfirmState();
}

function actionItemsForRow(row: any) {
    if (props.filters.trash) {
        return [
            { key: 'restore', label: 'Restore' },
            { key: 'force-delete', label: 'Delete Permanently', destructive: true },
        ];
    }

    return [
        { key: 'edit', label: 'Edit' },
        {
            key: 'impersonate',
            label: 'Impersonate User',
            show: canImpersonateRow(row),
        },
        { key: 'delete', label: 'Delete', destructive: true },
    ];
}

function canImpersonateRow(row: any) {
    const currentUserId = page.props.auth?.user?.id;
    const isImpersonating = Boolean(
        page.props.auth?.impersonation?.is_impersonating,
    );

    return (
        !isImpersonating && row.id !== currentUserId && row.status === 'active'
    );
}

function handleRowAction(actionKey: string, row: any) {
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
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl"
                    >
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
                            class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-slate-700"
                        >
                            {{
                                filters.trash ? 'Back to Active' : 'Recycle Bin'
                            }}
                        </Link>

                        <Button
                            v-if="filters.trash"
                            type="button"
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
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
                class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm"
            >
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search by name or email"
                    class="max-w-md dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
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
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3">
                        <Avatar class="h-8 w-8 border border-slate-200 dark:border-slate-700">
                            <AvatarImage
                                v-if="row.photo_url"
                                :src="row.photo_url"
                                :alt="row.name"
                            />
                            <AvatarFallback
                                class="bg-indigo-50 dark:bg-indigo-950/40 text-[10px] font-bold text-indigo-700 dark:text-indigo-300 uppercase"
                            >
                                {{
                                    row.name
                                        ?.split(' ')
                                        .map((n: string) => n[0])
                                        .join('')
                                        .slice(0, 2)
                                }}
                            </AvatarFallback>
                        </Avatar>
                        <span class="font-medium text-slate-900 dark:text-slate-100">{{
                            row.name
                        }}</span>
                    </div>
                </template>

                <template #cell-email="{ value }">
                    <span class="text-slate-700 dark:text-slate-300">{{ value }}</span>
                </template>

                <template #cell-roles="{ row }">
                    <span class="text-slate-700 dark:text-slate-300">{{ row.roles?.join(', ') || '—' }}</span>
                </template>

                <template #cell-permissions="{ row }">
                    <span class="text-slate-700 dark:text-slate-300">{{ row.permissions?.join(', ') || '—' }}</span>
                </template>

                <template #cell-created_at="{ value }">
                    <span class="text-slate-700 dark:text-slate-300">
                        {{ value ? new Date(value).toLocaleString() : '—' }}
                    </span>
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
