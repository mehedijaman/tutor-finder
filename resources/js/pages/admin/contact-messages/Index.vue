<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Badge } from '@/components/ui/badge';
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
});

const breadcrumbs = [
    { title: 'Contact Messages', href: '/admin/contact-messages' },
];
const baseUrl = '/admin/contact-messages';

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'phone', label: 'Phone', sortable: true },
    { key: 'subject', label: 'Subject' },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Received', sortable: true },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
const confirmOpen = ref(false);
const confirmTitle = ref('');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const pendingStatus = ref<string | null>(null);
const pendingMessage = ref<any>(null);
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

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
            q: search.value,
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
            sort: props.filters.sort ?? 'created_at',
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

function handleSort(columnKey: string) {
    const nextDirection =
        props.filters.sort === columnKey && props.filters.direction === 'asc'
            ? 'desc'
            : 'asc';

    applyFilters({ sort: columnKey, direction: nextDirection, page: 1 });
}

function actionItemsForRow(row: any) {
    return [
        { key: 'view', label: 'View' },
        {
            key: 'toggle-status',
            label: row.status === 'closed' ? 'Reopen' : 'Mark Closed',
        },
    ];
}

function handleRowAction(actionKey: string, row: any) {
    if (actionKey === 'view') {
        router.visit(`/admin/contact-messages/${row.id}`);
        return;
    }

    if (actionKey === 'toggle-status') {
        openStatusConfirm(row);
    }
}

function openStatusConfirm(row: any) {
    pendingMessage.value = row;
    pendingStatus.value = row.status === 'closed' ? 'open' : 'closed';

    if (pendingStatus.value === 'closed') {
        confirmTitle.value = 'Mark as closed';
        confirmDescription.value = 'This message will be marked as closed.';
        confirmLabel.value = 'Mark Closed';
    } else {
        confirmTitle.value = 'Reopen message';
        confirmDescription.value = 'This message will be marked as open again.';
        confirmLabel.value = 'Reopen';
    }

    confirmOpen.value = true;
}

function resetConfirmState() {
    pendingMessage.value = null;
    pendingStatus.value = null;
}

function runConfirmedAction() {
    if (!pendingMessage.value || !pendingStatus.value) {
        return;
    }

    router.patch(
        `/admin/contact-messages/${pendingMessage.value.id}/status`,
        {
            status: pendingStatus.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                resetConfirmState();
            },
        },
    );

    confirmOpen.value = false;
}
</script>

<template>
    <Head title="Contact Messages" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h1
                            class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                        >
                            Contact Messages
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Open: {{ counts.open ?? 0 }} | Closed:
                            {{ counts.closed ?? 0 }} | Total:
                            {{ counts.all ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-2 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="grid gap-2">
                    <Label
                        for="contact-search"
                        class="text-slate-800 dark:text-slate-200"
                        >Search</Label
                    >
                    <Input
                        id="contact-search"
                        v-model="search"
                        type="text"
                        placeholder="Search by name, email, phone, or subject"
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    />
                </div>

                <div class="grid gap-2">
                    <Label class="text-slate-800 dark:text-slate-200"
                        >Status</Label
                    >
                    <Select v-model="statusFilter">
                        <SelectTrigger
                            class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <SelectValue placeholder="All status" />
                        </SelectTrigger>
                        <SelectContent
                            class="dark:border-slate-800 dark:bg-slate-900"
                        >
                            <SelectItem value="all">All status</SelectItem>
                            <SelectItem value="open">Open</SelectItem>
                            <SelectItem value="closed">Closed</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                :sort-by="filters.sort"
                :sort-direction="filters.direction"
                empty-text="No contact messages found."
                @sort="handleSort"
            >
                <template #cell-name="{ value }">
                    <span
                        class="font-medium text-slate-900 dark:text-slate-100"
                        >{{ value }}</span
                    >
                </template>

                <template #cell-email="{ value }">
                    <span class="text-slate-700 dark:text-slate-300">{{
                        value
                    }}</span>
                </template>

                <template #cell-phone="{ value }">
                    <span class="text-slate-700 dark:text-slate-300">{{
                        value || '—'
                    }}</span>
                </template>

                <template #cell-subject="{ value }">
                    <span
                        class="font-medium text-slate-900 dark:text-slate-100"
                        >{{ value }}</span
                    >
                </template>

                <template #cell-status="{ row }">
                    <Badge
                        :variant="
                            row.status === 'open' ? 'default' : 'secondary'
                        "
                    >
                        {{ row.status }}
                    </Badge>
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
            @confirm="runConfirmedAction"
            @cancel="resetConfirmState"
        />
    </AdminLayout>
</template>
