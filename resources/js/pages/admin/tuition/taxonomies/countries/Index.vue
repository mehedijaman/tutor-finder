<script setup lang="ts">
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
});

const breadcrumbs = [
    { title: 'Countries', href: '/admin/tuition/taxonomies/countries' },
];
const baseUrl = '/admin/tuition/taxonomies/countries';

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'slug', label: 'Slug' },
    { key: 'status', label: 'Status' },
    { key: 'cities_count', label: 'Cities' },
    { key: 'updated_at', label: 'Updated At' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
const confirmOpen = ref(false);
const confirmTitle = ref('Confirm Action');
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
        confirmTitle.value = 'Delete Country';
        confirmDescription.value = 'This will move the country to recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'status') {
        const nextStatus = payload.status;
        const isDeactivate = nextStatus === 'inactive';
        confirmTitle.value = isDeactivate
            ? 'Deactivate Country'
            : 'Activate Country';
        confirmDescription.value = isDeactivate
            ? 'This country will become inactive.'
            : 'This country will become active.';
        confirmLabel.value = isDeactivate ? 'Deactivate' : 'Activate';
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Country';
        confirmDescription.value =
            'This will restore the country from recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Country';
        confirmDescription.value = 'This action cannot be undone.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'This will permanently remove all eligible trashed countries.';
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
        router.delete(`/admin/tuition/taxonomies/countries/${row.id}`);
    }

    if (action === 'status' && row) {
        router.patch(`/admin/tuition/taxonomies/countries/${row.id}/status`, {
            status: payload.status,
        });
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/tuition/taxonomies/countries/${row.id}/restore`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/tuition/taxonomies/countries/${row.id}/force`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/tuition/taxonomies/countries/recycle-bin/empty');
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
            key: 'status',
            label: row.status === 'active' ? 'Deactivate' : 'Activate',
        },
        { key: 'delete', label: 'Delete', destructive: true },
    ];
}

function handleRowAction(actionKey, row) {
    if (actionKey === 'edit') {
        router.visit(`/admin/tuition/taxonomies/countries/${row.id}/edit`);

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
    <Head title="Countries" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold sm:text-3xl">
                        {{
                            filters.trash ? 'Country Recycle Bin' : 'Countries'
                        }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Active: {{ counts.active ?? 0 }} | Trash:
                        {{ counts.trash ?? 0 }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="filters.trash ? baseUrl : `${baseUrl}?trash=1`"
                        class="rounded-md border px-4 py-2 text-sm"
                    >
                        {{ filters.trash ? 'Back to Active' : 'Recycle Bin' }}
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
                        href="/admin/tuition/taxonomies/countries/create"
                        class="rounded-md bg-black px-4 py-2 text-sm text-white"
                    >
                        Create Country
                    </Link>
                </div>
            </div>

            <div
                class="grid gap-3 rounded-xl border bg-white p-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search by name or slug"
                    class="sm:col-span-2"
                />

                <Select v-model="statusFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All Statuses" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Statuses</SelectItem>
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

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No countries found."
            >
                <template #cell-status="{ row }">
                    <Badge
                        :variant="
                            row.status === 'active' ? 'default' : 'secondary'
                        "
                    >
                        {{ row.status }}
                    </Badge>
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
