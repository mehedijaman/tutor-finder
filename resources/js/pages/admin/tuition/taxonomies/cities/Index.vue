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
    countries: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Cities', href: '/admin/tuition/taxonomies/cities' },
];
const baseUrl = '/admin/tuition/taxonomies/cities';

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'country_name', label: 'Country' },
    { key: 'slug', label: 'Slug' },
    { key: 'status', label: 'Status' },
    { key: 'areas_count', label: 'Areas' },
    { key: 'updated_at', label: 'Updated At' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
const countryFilter = ref(
    props.filters.country_id ? String(props.filters.country_id) : 'all',
);
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

watch(
    () => props.filters.country_id,
    (value) => {
        const normalized = value ? String(value) : 'all';

        if (normalized !== countryFilter.value) {
            countryFilter.value = normalized;
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

watch(countryFilter, (value) => {
    applyFilters({ country_id: value === 'all' ? '' : value, page: 1 });
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
            country_id:
                countryFilter.value === 'all' ? '' : countryFilter.value,
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
        confirmTitle.value = 'Delete City';
        confirmDescription.value = 'This will move the city to recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'status') {
        const nextStatus = payload.status;
        const isDeactivate = nextStatus === 'inactive';
        confirmTitle.value = isDeactivate ? 'Deactivate City' : 'Activate City';
        confirmDescription.value = isDeactivate
            ? 'This city will become inactive.'
            : 'This city will become active.';
        confirmLabel.value = isDeactivate ? 'Deactivate' : 'Activate';
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore City';
        confirmDescription.value =
            'This will restore the city from recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete City';
        confirmDescription.value = 'This action cannot be undone.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'This will permanently remove all eligible trashed cities.';
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
        router.delete(`/admin/tuition/taxonomies/cities/${row.id}`);
    }

    if (action === 'status' && row) {
        router.patch(`/admin/tuition/taxonomies/cities/${row.id}/status`, {
            status: payload.status,
        });
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/tuition/taxonomies/cities/${row.id}/restore`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/tuition/taxonomies/cities/${row.id}/force`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/tuition/taxonomies/cities/recycle-bin/empty');
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
        router.visit(`/admin/tuition/taxonomies/cities/${row.id}/edit`);

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
    <Head title="Cities" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-1">
                    <h1 class="text-2xl sm:text-3xl font-semibold">
                        {{ filters.trash ? 'City Recycle Bin' : 'Cities' }}
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
                        href="/admin/tuition/taxonomies/cities/create"
                        class="rounded-md bg-black px-4 py-2 text-sm text-white"
                    >
                        Create City
                    </Link>
                </div>
            </div>

            <div
                class="grid gap-3 rounded-xl border bg-white p-4 sm:grid-cols-2 lg:grid-cols-4"
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

                <Select v-model="countryFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All Countries" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Countries</SelectItem>
                        <SelectItem
                            v-for="country in countries"
                            :key="country.id"
                            :value="String(country.id)"
                        >
                            {{ country.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No cities found."
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
