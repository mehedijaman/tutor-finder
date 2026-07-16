<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Badge } from '@/components/ui/badge';
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
    counts: {
        type: Object,
        default: () => ({}),
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [{ title: 'Pages', href: '/admin/pages' }];
const baseUrl = '/admin/pages';

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'slug', label: 'Slug' },
    { key: 'status', label: 'Status' },
    { key: 'is_system', label: 'Type' },
    { key: 'updated_at', label: 'Updated At' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
const confirmOpen = ref(false);
const confirmTitle = ref('');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(false);
const pendingAction = ref(null);
let searchDebounceTimer = null;

watch(
    () => props.filters.q,
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
        const normalized = value || 'all';

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
        applyFilters({ q: value, page: 1 });
    }, 350);
});

watch(statusFilter, (value) => {
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
            q: search.value,
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function openConfirm(action, row = null, payload = {}) {
    pendingAction.value = { action, row, payload };
    confirmTitle.value = 'Confirm Action';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;

    if (action === 'delete') {
        confirmTitle.value = 'Delete Page';
        confirmDescription.value = 'This will move the page to recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'status') {
        const nextStatus = payload.status;
        const isDeactivate = nextStatus === 'inactive';

        confirmTitle.value = isDeactivate ? 'Deactivate Page' : 'Activate Page';
        confirmDescription.value = isDeactivate
            ? 'This page will no longer be visible on the website.'
            : 'This page will be visible on the website.';
        confirmLabel.value = isDeactivate ? 'Deactivate' : 'Activate';
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Page';
        confirmDescription.value =
            'This will restore the page from recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Page';
        confirmDescription.value = 'This action cannot be undone.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'This will permanently remove all trashed pages (except system pages).';
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

    const { action, row, payload } = pendingAction.value;

    if (action === 'delete' && row) {
        router.delete(`/admin/pages/${row.id}`);
    }

    if (action === 'status' && row) {
        router.patch(`/admin/pages/${row.id}/status`, {
            status: payload.status,
        });
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/pages/${row.id}/restore`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/pages/${row.id}/force`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/pages/recycle-bin/empty');
    }

    confirmOpen.value = false;
    resetConfirmState();
}

function actionItemsForRow(row) {
    if (props.filters.trash) {
        const items = [{ key: 'restore', label: 'Restore' }];

        if (!row.is_system) {
            items.push({
                key: 'force-delete',
                label: 'Permanently Delete',
                destructive: true,
            });
        }

        return items;
    }

    const items = [
        { key: 'edit', label: 'Edit' },
        {
            key: 'status',
            label: row.status === 'active' ? 'Deactivate' : 'Activate',
        },
    ];

    if (!row.is_system) {
        items.push({ key: 'delete', label: 'Delete', destructive: true });
    }

    return items;
}

function handleRowAction(actionKey, row) {
    if (actionKey === 'edit') {
        router.visit(`/admin/pages/${row.id}/edit`);

        return;
    }

    if (actionKey === 'status') {
        const nextStatus = row.status === 'active' ? 'inactive' : 'active';

        openConfirm('status', row, { status: nextStatus });

        return;
    }

    if (
        actionKey === 'delete' ||
        actionKey === 'restore' ||
        actionKey === 'force-delete'
    ) {
        openConfirm(actionKey, row);
    }
}
</script>

<template>
    <Head title="Pages" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">
                            {{ filters.trash ? 'Page Recycle Bin' : 'Pages' }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Active: {{ counts.active ?? 0 }} | Trash:
                            {{ counts.trash ?? 0 }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            :href="
                                filters.trash
                                    ? '/admin/pages'
                                    : '/admin/pages?trash=1'
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
                            href="/admin/pages/create"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            Create Page
                        </Link>
                    </div>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-2"
            >
                <div class="grid gap-2">
                    <Label for="page-search">Search</Label>
                    <Input
                        id="page-search"
                        v-model="search"
                        type="text"
                        placeholder="Search by title or slug"
                    />
                </div>

                <div class="grid gap-2">
                    <Label>Status</Label>
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
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No pages found."
            >
                <template #cell-title="{ row }">
                    <div class="max-w-xl">
                        <p class="line-clamp-1 font-medium">{{ row.title }}</p>
                    </div>
                </template>

                <template #cell-slug="{ row }">
                    <code
                        class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-600"
                    >
                        {{ row.slug }}
                    </code>
                </template>

                <template #cell-status="{ row }">
                    <Badge
                        :variant="
                            row.status === 'active' ? 'default' : 'secondary'
                        "
                    >
                        {{ row.status }}
                    </Badge>
                </template>

                <template #cell-is_system="{ row }">
                    <Badge v-if="row.is_system" variant="outline">
                        System
                    </Badge>
                    <span v-else class="text-sm text-muted-foreground"
                        >Custom</span
                    >
                </template>

                <template #cell-updated_at="{ value }">
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
