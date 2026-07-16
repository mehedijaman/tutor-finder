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
    audienceOptions: {
        type: Array,
        default: () => [],
    },
});

const breadcrumbs = [{ title: 'Notices', href: '/admin/notices' }];
const baseUrl = '/admin/notices';

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'audience', label: 'Audience' },
    { key: 'status', label: 'Status' },
    { key: 'published_at', label: 'Published' },
    { key: 'expires_at', label: 'Expires' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const audienceFilter = ref(props.filters.audience || 'all');
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
    () => props.filters.audience,
    (value) => {
        const normalized = value || 'all';

        if (normalized !== audienceFilter.value) {
            audienceFilter.value = normalized;
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

watch(audienceFilter, (value) => {
    applyFilters({ audience: value === 'all' ? '' : value, page: 1 });
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
            audience:
                audienceFilter.value === 'all' ? '' : audienceFilter.value,
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

function openConfirm(action, row = null) {
    pendingAction.value = { action, row };
    confirmTitle.value = 'Confirm Action';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;

    if (action === 'delete') {
        confirmTitle.value = 'Delete Notice';
        confirmDescription.value = 'This will move the notice to recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Notice';
        confirmDescription.value =
            'This will restore the notice from recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Notice';
        confirmDescription.value = 'This action cannot be undone.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'This will permanently remove all trashed notices.';
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

    if (action === 'delete' && row) {
        router.delete(`/admin/notices/${row.id}`);
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/notices/${row.id}/restore`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/notices/${row.id}/force`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/notices/recycle-bin/empty');
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
        router.visit(`/admin/notices/${row.id}/edit`);

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

function formatAudience(value) {
    return String(value || '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function formatDate(value) {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString();
}

function getStatusBadge(row) {
    if (!row.is_active) {
        return { label: 'Inactive', variant: 'secondary' };
    }

    const now = new Date();
    const expiresAt = row.expires_at ? new Date(row.expires_at) : null;

    if (expiresAt && expiresAt < now) {
        return { label: 'Expired', variant: 'destructive' };
    }

    return { label: 'Active', variant: 'default' };
}
</script>

<template>
    <Head title="Notices" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">
                            {{
                                filters.trash ? 'Notice Recycle Bin' : 'Notices'
                            }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Active: {{ counts.active ?? 0 }} | Expired:
                            {{ counts.expired ?? 0 }} | Trash:
                            {{ counts.trash ?? 0 }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            :href="
                                filters.trash
                                    ? '/admin/notices'
                                    : '/admin/notices?trash=1'
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
                            href="/admin/notices/create"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            Create Notice
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
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-3"
            >
                <div class="grid gap-2 md:col-span-1">
                    <Label for="notice-search">Search</Label>
                    <Input
                        id="notice-search"
                        v-model="search"
                        type="text"
                        placeholder="Search by title"
                    />
                </div>

                <div class="grid gap-2">
                    <Label>Audience</Label>
                    <Select v-model="audienceFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All audiences" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All audiences</SelectItem>
                            <SelectItem
                                v-for="option in audienceOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label>Status</Label>
                    <Select v-model="statusFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All statuses</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                            <SelectItem value="expired">Expired</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No notices found."
            >
                <template #cell-title="{ row }">
                    <div class="max-w-xl">
                        <p class="line-clamp-2 font-medium">{{ row.title }}</p>
                    </div>
                </template>

                <template #cell-audience="{ row }">
                    {{ formatAudience(row.audience) }}
                </template>

                <template #cell-status="{ row }">
                    <Badge :variant="getStatusBadge(row).variant">
                        {{ getStatusBadge(row).label }}
                    </Badge>
                </template>

                <template #cell-published_at="{ value }">
                    {{ formatDate(value) }}
                </template>

                <template #cell-expires_at="{ value }">
                    {{ formatDate(value) }}
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
