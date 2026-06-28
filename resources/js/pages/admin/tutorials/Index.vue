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
        type: Array as () => { value: string; label: string }[],
        default: () => [],
    },
});

const breadcrumbs = [{ title: 'Tutorials', href: '/admin/tutorials' }];
const baseUrl = '/admin/tutorials';

const columns = [
    { key: 'thumbnail', label: '', cellClass: 'w-16' },
    { key: 'title', label: 'Title' },
    { key: 'audience', label: 'Audience' },
    { key: 'is_active', label: 'Status' },
    { key: 'created_at', label: 'Created At' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const audienceFilter = ref(props.filters.audience || 'all');
const confirmOpen = ref(false);
const confirmTitle = ref('');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(false);
const pendingAction = ref<{
    action: string;
    row?: Record<string, unknown>;
    payload?: Record<string, unknown>;
} | null>(null);
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const audienceLabels: Record<string, string> = {
    all: 'All',
    tutor: 'For Tutor',
    guardian: 'For Guardian/Student',
};

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

onBeforeUnmount(() => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }
});

function applyFilters(overrides: Record<string, unknown> = {}) {
    router.get(
        baseUrl,
        {
            trash: props.filters.trash ? 1 : 0,
            q: search.value,
            audience:
                audienceFilter.value === 'all' ? '' : audienceFilter.value,
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function openConfirm(
    action: string,
    row: Record<string, unknown> | null = null,
    payload: Record<string, unknown> = {},
) {
    pendingAction.value = { action, row: row ?? undefined, payload };
    confirmTitle.value = 'Confirm Action';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;

    if (action === 'delete') {
        confirmTitle.value = 'Delete Tutorial';
        confirmDescription.value =
            'This will move the tutorial to the recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Tutorial';
        confirmDescription.value =
            'This will restore the tutorial from the recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Tutorial';
        confirmDescription.value = 'This action cannot be undone.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'This will permanently remove all trashed tutorials. This action cannot be undone.';
        confirmLabel.value = 'Empty Recycle Bin';
        confirmDestructive.value = true;
    }

    if (action === 'toggle-status') {
        const willDeactivate = payload.is_active === false;
        confirmTitle.value = willDeactivate
            ? 'Deactivate Tutorial'
            : 'Activate Tutorial';
        confirmDescription.value = willDeactivate
            ? 'This tutorial will no longer be visible on the website.'
            : 'This tutorial will be visible on the website.';
        confirmLabel.value = willDeactivate ? 'Deactivate' : 'Activate';
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
        router.delete(`/admin/tutorials/${row.id}`);
    }

    if (action === 'toggle-status' && row) {
        router.patch(`/admin/tutorials/${row.id}/status`, {
            is_active: payload?.is_active,
        });
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/tutorials/${row.id}/restore`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/tutorials/${row.id}/force`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/tutorials/recycle-bin/empty');
    }

    confirmOpen.value = false;
    resetConfirmState();
}

function actionItemsForRow(row: Record<string, unknown>) {
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
            key: 'toggle-status',
            label: row.is_active ? 'Deactivate' : 'Activate',
        },
        { key: 'delete', label: 'Delete', destructive: true },
    ];
}

function handleRowAction(actionKey: string, row: Record<string, unknown>) {
    if (actionKey === 'edit') {
        router.visit(`/admin/tutorials/${row.id}/edit`);
        return;
    }

    if (actionKey === 'toggle-status') {
        openConfirm('toggle-status', row, { is_active: !row.is_active });
        return;
    }

    if (actionKey === 'delete') {
        openConfirm('delete', row);
    }

    if (actionKey === 'restore') {
        openConfirm('restore', row);
    }

    if (actionKey === 'force-delete') {
        openConfirm('force-delete', row);
    }
}
</script>

<template>
    <Head title="Tutorials" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h1 class="text-2xl font-semibold tracking-tight">
                            {{
                                filters.trash
                                    ? 'Tutorial Recycle Bin'
                                    : 'Tutorials'
                            }}
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
                                    ? '/admin/tutorials'
                                    : '/admin/tutorials?trash=1'
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
                            href="/admin/tutorials/create"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            Create Tutorial
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
                    <Label for="tutorial-search">Search</Label>
                    <Input
                        id="tutorial-search"
                        v-model="search"
                        type="text"
                        placeholder="Search by title or slug"
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
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No tutorials found."
            >
                <template #cell-thumbnail="{ row }">
                    <div class="h-10 w-16 overflow-hidden rounded bg-slate-100">
                        <img
                            v-if="row.thumbnail_url"
                            :src="row.thumbnail_url"
                            :alt="row.title"
                            class="h-full w-full object-cover"
                        />
                    </div>
                </template>

                <template #cell-title="{ row }">
                    <div class="max-w-xl">
                        <p class="line-clamp-1 font-medium">{{ row.title }}</p>
                    </div>
                </template>

                <template #cell-audience="{ row }">
                    <Badge variant="outline">
                        {{
                            audienceLabels[
                                row.audience?.value ?? row.audience
                            ] ?? row.audience
                        }}
                    </Badge>
                </template>

                <template #cell-is_active="{ row }">
                    <Badge :variant="row.is_active ? 'default' : 'secondary'">
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </template>

                <template #cell-created_at="{ value }">
                    {{ value ? new Date(value).toLocaleString() : '—' }}
                </template>

                <template #cell-actions="{ row }">
                    <RowActionsDropdown
                        :actions="actionItemsForRow(row)"
                        @select="
                            (action: string) => handleRowAction(action, row)
                        "
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
