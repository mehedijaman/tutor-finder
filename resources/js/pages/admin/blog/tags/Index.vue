<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Badge } from '@/components/ui/badge';
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
    counts: {
        type: Object,
        default: () => ({}),
    },
});

const breadcrumbs = [{ title: 'Blog Tags', href: '/admin/blog/tags' }];
const baseUrl = '/admin/blog/tags';

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'slug', label: 'Slug', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'posts_count', label: 'Posts' },
    { key: 'updated_at', label: 'Updated', sortable: true },
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

function openConfirm(action, row = null) {
    pendingAction.value = { action, row };
    confirmTitle.value = 'Confirm Action';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;

    if (action === 'delete') {
        confirmTitle.value = 'Delete Tag';
        confirmDescription.value = 'This will move the tag to recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Tag';
        confirmDescription.value =
            'This will restore the tag from recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Tag';
        confirmDescription.value = 'This action cannot be undone.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'This will permanently remove all trashed tags.';
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
        router.delete(`/admin/blog/tags/${row.id}`);
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/blog/tags/${row.id}/restore`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/blog/tags/${row.id}/force`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/blog/tags/recycle-bin/empty');
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
        router.visit(`/admin/blog/tags/${row.id}/edit`);
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
    <Head title="Blog Tags" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-1">
                    <h1 class="text-2xl sm:text-3xl font-semibold">
                        {{ filters.trash ? 'Tag Recycle Bin' : 'Blog Tags' }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Active: {{ counts.active ?? 0 }} | Trash:
                        {{ counts.trash ?? 0 }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="
                            filters.trash
                                ? '/admin/blog/tags'
                                : '/admin/blog/tags?trash=1'
                        "
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
                        href="/admin/blog/tags/create"
                        class="rounded-md bg-black px-4 py-2 text-sm text-white"
                    >
                        Create Tag
                    </Link>
                </div>
            </div>

            <div class="rounded-xl border bg-white p-4">
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search by name or slug"
                    class="max-w-md"
                />
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                :sort-by="filters.sort"
                :sort-direction="filters.direction"
                empty-text="No tags found."
                @sort="handleSort"
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
