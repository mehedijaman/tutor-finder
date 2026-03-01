<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

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

const breadcrumbs = [{ title: 'SMS Settings', href: '/settings/sms' }];
const baseUrl = '/settings/sms';

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'provider', label: 'Provider', sortable: true },
    { key: 'credential_keys', label: 'Credentials' },
    { key: 'is_active', label: 'Status' },
    { key: 'is_default', label: 'Default' },
    { key: 'updated_at', label: 'Updated', sortable: true },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.search ?? '');
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
            search: search.value,
            sort: props.filters.sort ?? 'updated_at',
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

function handleRowAction(actionKey, row) {
    if (actionKey === 'edit') {
        router.visit(`/settings/sms/${row.id}/edit`);
    }
}
</script>

<template>
    <Head title="SMS Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout full-width>
            <div class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl font-semibold">SMS Settings</h1>
                    <Link
                        href="/settings/sms/create"
                        class="rounded-md bg-black px-4 py-2 text-sm text-white"
                    >
                        Add SMS Setting
                    </Link>
                </div>

                <div class="rounded-xl border bg-white p-4">
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Search by name or provider"
                        class="max-w-md"
                    />
                </div>

                <DataTable
                    :items="items"
                    :columns="columns"
                    :sort-by="filters.sort"
                    :sort-direction="filters.direction"
                    empty-text="No SMS settings found."
                    @sort="handleSort"
                >
                    <template #cell-credential_keys="{ row }">
                        {{ row.credential_keys?.length ? row.credential_keys.join(', ') : '—' }}
                    </template>

                    <template #cell-is_active="{ row }">
                        <span :class="row.is_active ? 'text-emerald-700' : 'text-rose-700'">
                            {{ row.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </template>

                    <template #cell-is_default="{ row }">
                        {{ row.is_default ? 'Yes' : 'No' }}
                    </template>

                    <template #cell-updated_at="{ value }">
                        {{ value ? new Date(value).toLocaleString() : '—' }}
                    </template>

                    <template #cell-actions="{ row }">
                        <RowActionsDropdown
                            :actions="[{ key: 'edit', label: 'Edit' }]"
                            @select="(action) => handleRowAction(action, row)"
                        />
                    </template>
                </DataTable>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
