<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Briefcase,
    CheckCircle2,
    Clock,
    FileText,
    LayoutDashboard,
    Plus,
    Search,
    Trash2,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    counts: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
    guardianOptions: { type: Array, default: () => [] },
    pageTitle: { type: String, default: 'All Jobs' },
});

const breadcrumbs = [{ title: 'Jobs', href: '/admin/jobs' }];

const presetStatus = computed(() => props.filters.preset_status || '');
const baseUrl = computed(() => {
    if (presetStatus.value === 'pending') {
        return '/admin/jobs/pending';
    }

    if (presetStatus.value === 'live') {
        return '/admin/jobs/live';
    }

    if (presetStatus.value === 'expired') {
        return '/admin/jobs/expired';
    }

    if (presetStatus.value === 'confirmed') {
        return '/admin/jobs/confirmed';
    }

    if (presetStatus.value === 'cancelled') {
        return '/admin/jobs/cancelled';
    }

    return '/admin/jobs';
});

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'guardian_name', label: 'Guardian' },
    { key: 'category_info', label: 'Type / Category' },
    { key: 'location_info', label: 'Location' },
    { key: 'applications_count', label: 'Engagement' },
    { key: 'hiring_outcome', label: 'Outcome' },
    { key: 'status', label: 'Status' },
    { key: 'dates', label: 'Workflow Timestamps' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
const guardianFilter = ref(
    props.filters.guardian_id ? String(props.filters.guardian_id) : 'all',
);
const sortBy = ref(props.filters.sort || 'updated_at');
const direction = ref(props.filters.direction || 'desc');

const confirmOpen = ref(false);
const confirmTitle = ref('Confirm Action');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(false);
const pendingAction = ref<{ action: string; row: any } | null>(null);
let searchDebounceTimer: any = null;

const transitionMap = {
    pending: ['live', 'cancelled'],
    live: ['confirmed', 'cancelled', 'closed'],
    confirmed: ['closed'],
    cancelled: [],
    closed: [],
};

const statusLabelMap = {
    pending: 'Pending',
    live: 'Live',
    confirmed: 'Confirmed',
    cancelled: 'Cancelled',
    closed: 'Closed',
};

watch(
    () => props.filters.q,
    (value) => {
        const normalized = value ?? '';

        if (search.value !== normalized) {
            search.value = normalized;
        }
    },
);

watch(
    () => props.filters.status,
    (value) => {
        const normalized = value || 'all';

        if (statusFilter.value !== normalized) {
            statusFilter.value = normalized;
        }
    },
);

watch(
    () => props.filters.guardian_id,
    (value) => {
        const normalized = value ? String(value) : 'all';

        if (guardianFilter.value !== normalized) {
            guardianFilter.value = normalized;
        }
    },
);

watch(
    () => props.filters.sort,
    (value) => {
        const normalized = value || 'updated_at';

        if (sortBy.value !== normalized) {
            sortBy.value = normalized;
        }
    },
);

watch(
    () => props.filters.direction,
    (value) => {
        const normalized = value || 'desc';

        if (direction.value !== normalized) {
            direction.value = normalized;
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
    if (presetStatus.value) {
        return;
    }

    applyFilters({ status: value === 'all' ? '' : value, page: 1 });
});

watch(guardianFilter, (value) => {
    applyFilters({ guardian_id: value === 'all' ? '' : value, page: 1 });
});

watch(sortBy, (value) => {
    applyFilters({ sort: value, page: 1 });
});

watch(direction, (value) => {
    applyFilters({ direction: value, page: 1 });
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }
});

function applyFilters(overrides = {}) {
    router.get(
        baseUrl.value,
        {
            trash: props.filters.trash ? 1 : 0,
            q: search.value,
            status: presetStatus.value
                ? ''
                : statusFilter.value === 'all'
                  ? ''
                  : statusFilter.value,
            guardian_id:
                guardianFilter.value === 'all' ? '' : guardianFilter.value,
            sort: sortBy.value,
            direction: direction.value,
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function statusActionLabel(nextStatus: string) {
    return `Mark as ${statusLabelMap[nextStatus as keyof typeof statusLabelMap] ?? nextStatus}`;
}

function actionItemsForRow(row: any) {
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

    const actions = [
        { key: 'view', label: 'View Details' },
        { key: 'applications', label: 'Manage Applications' },
        { key: 'edit', label: 'Edit Job Details' },
        { key: 'delete', label: 'Delete', destructive: true },
    ];

    if (
        row.requested_tutor_id &&
        row.status !== 'confirmed' &&
        row.status !== 'closed' &&
        !row.has_assignment
    ) {
        actions.unshift({ key: 'settle', label: 'Settle Direct Request' });
    }

    if (row.status === 'pending') {
        actions.unshift({ key: 'approve', label: 'Approve (Mark Live)' });
    }

    const transitions =
        transitionMap[row.status as keyof typeof transitionMap] ?? [];

    transitions.forEach((nextStatus: string) => {
        if (row.status === 'pending' && nextStatus === 'live') {
            return;
        }

        actions.push({
            key: `status-${nextStatus}`,
            label: statusActionLabel(nextStatus),
            destructive: nextStatus === 'cancelled',
        });
    });

    return actions;
}

function handleRowAction(actionKey: string, row: any) {
    if (actionKey === 'view') {
        router.visit(`/admin/jobs/${row.id}`);

        return;
    }

    if (actionKey === 'settle') {
        router.visit(`/admin/jobs/${row.id}/settle`);

        return;
    }

    if (actionKey === 'applications') {
        router.visit(`/admin/jobs/${row.id}/applications`);

        return;
    }

    if (actionKey === 'edit') {
        router.visit(`/admin/jobs/${row.id}/edit`);

        return;
    }

    openConfirm(actionKey, row);
}

function openConfirm(action: string, row: any = null) {
    pendingAction.value = { action, row };
    confirmTitle.value = 'Confirm Action';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;

    if (action === 'delete') {
        confirmTitle.value = 'Delete Job';
        confirmDescription.value = 'This will move the job to recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'approve') {
        confirmTitle.value = 'Approve Job';
        confirmDescription.value =
            'This will move the job from pending to live.';
        confirmLabel.value = 'Approve';
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Job';
        confirmDescription.value =
            'This will restore the job from recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Job';
        confirmDescription.value = 'This action cannot be undone.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'This will permanently delete all trashed jobs.';
        confirmLabel.value = 'Empty Recycle Bin';
        confirmDestructive.value = true;
    }

    if (action.startsWith('status-')) {
        const nextStatus = action.replace('status-', '');
        const nextStatusLabel =
            statusLabelMap[nextStatus as keyof typeof statusLabelMap] ??
            nextStatus;

        confirmTitle.value = `Change Status to ${nextStatusLabel}`;
        confirmDescription.value = `This will change the job status to ${nextStatusLabel}.`;
        confirmLabel.value = `Set ${nextStatusLabel}`;
        confirmDestructive.value = nextStatus === 'cancelled';
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

    if (action === 'approve' && row) {
        router.patch(`/admin/jobs/${row.id}/approve`);
    }

    if (action.startsWith('status-') && row) {
        const nextStatus = action.replace('status-', '');

        router.patch(`/admin/jobs/${row.id}/status`, {
            status: nextStatus,
            reason:
                nextStatus === 'cancelled'
                    ? 'Cancelled by admin from listing.'
                    : '',
        });
    }

    if (action === 'delete' && row) {
        router.delete(`/admin/jobs/${row.id}`);
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/jobs/${row.id}/restore`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/jobs/${row.id}/force`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/jobs/recycle-bin/empty');
    }

    confirmOpen.value = false;
    resetConfirmState();
}

function statusBadge(row: any) {
    if (row.status === 'live' && row.is_expired) {
        return {
            label: 'Expired',
            variant: 'destructive',
            class: 'bg-rose-50 text-rose-700 border-rose-100',
        };
    }

    if (row.status === 'live') {
        return {
            label: 'Live',
            variant: 'default',
            class: 'bg-emerald-50 text-emerald-700 border-emerald-100',
        };
    }

    if (row.status === 'pending') {
        return {
            label: 'Pending',
            variant: 'secondary',
            class: 'bg-amber-50 text-amber-700 border-amber-100',
        };
    }

    if (row.status === 'confirmed') {
        return {
            label: 'Confirmed',
            variant: 'outline',
            class: 'bg-indigo-50 text-indigo-700 border-indigo-100',
        };
    }

    if (row.status === 'cancelled') {
        return {
            label: 'Cancelled',
            variant: 'outline',
            class: 'bg-slate-50 text-slate-700 border-slate-100',
        };
    }

    return {
        label: row.status,
        variant: 'secondary',
        class: '',
    };
}
</script>

<template>
    <Head :title="pageTitle" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4 sm:p-6 lg:p-8">
            <div class="flex flex-wrap items-center justify-between gap-6 pb-2">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <div
                            class="rounded-xl bg-blue-600 p-2 text-white shadow-lg shadow-blue-200"
                        >
                            <Briefcase class="h-6 w-6" />
                        </div>
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl"
                        >
                            {{ pageTitle }}
                        </h1>
                    </div>
                    <p class="pl-11 text-sm text-slate-500">
                        Manage and track tuition recruitment opportunities
                        across the platform.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 pl-11 md:pl-0">
                    <Link
                        :href="filters.trash ? baseUrl : `${baseUrl}?trash=1`"
                        class="group inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all hover:bg-slate-50 hover:shadow-md active:scale-95 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                    >
                        <Trash2
                            class="h-4 w-4 transition-colors group-hover:text-rose-500"
                        />
                        {{ filters.trash ? 'Back to Active' : 'Recycle Bin' }}
                        <Badge
                            v-if="counts.trash_count > 0 && !filters.trash"
                            variant="secondary"
                            class="ml-1 bg-rose-50 text-rose-600"
                            >{{ counts.trash_count }}</Badge
                        >
                    </Link>

                    <Button
                        v-if="filters.trash"
                        type="button"
                        variant="destructive"
                        class="rounded-xl px-5 shadow-lg shadow-rose-100"
                        @click="openConfirm('empty-recycle-bin')"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Empty Bin
                    </Button>

                    <Link
                        v-if="!filters.trash"
                        href="/admin/jobs/create"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-200 transition-all hover:bg-blue-700 hover:shadow-blue-300 active:scale-95"
                    >
                        <Plus class="h-5 w-5" />
                        Create Job
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <Card
                    class="border-blue-100 bg-blue-50/30 transition-all hover:shadow-md"
                >
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-blue-100 p-2 text-blue-600"
                            >
                                <FileText class="h-5 w-5" />
                            </div>
                            <div class="space-y-0.5">
                                <p
                                    class="text-xs font-bold tracking-wider text-blue-600/70 uppercase"
                                >
                                    Total
                                </p>
                                <p class="text-2xl font-black text-blue-700">
                                    {{ counts.total_count ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="border-amber-100 bg-amber-50/30 transition-all hover:shadow-md"
                >
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-amber-100 p-2 text-amber-600"
                            >
                                <Clock class="h-5 w-5" />
                            </div>
                            <div class="space-y-0.5">
                                <p
                                    class="text-xs font-bold tracking-wider text-amber-600/70 uppercase"
                                >
                                    Pending
                                </p>
                                <p class="text-2xl font-black text-amber-700">
                                    {{ counts.pending_count ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="border-emerald-100 bg-emerald-50/30 transition-all hover:shadow-md"
                >
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-emerald-100 p-2 text-emerald-600"
                            >
                                <LayoutDashboard class="h-5 w-5" />
                            </div>
                            <div class="space-y-0.5">
                                <p
                                    class="text-xs font-bold tracking-wider text-emerald-600/70 uppercase"
                                >
                                    Live
                                </p>
                                <p class="text-2xl font-black text-emerald-700">
                                    {{ counts.live_count ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="border-indigo-100 bg-indigo-50/30 transition-all hover:shadow-md"
                >
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-indigo-100 p-2 text-indigo-600"
                            >
                                <CheckCircle2 class="h-5 w-5" />
                            </div>
                            <div class="space-y-0.5">
                                <p
                                    class="text-xs font-bold tracking-wider text-indigo-600/70 uppercase"
                                >
                                    Confirmed
                                </p>
                                <p class="text-2xl font-black text-indigo-700">
                                    {{ counts.confirmed_count ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="hidden border-rose-100 bg-rose-50/30 transition-all hover:shadow-md lg:block"
                >
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-rose-100 p-2 text-rose-600"
                            >
                                <Trash2 class="h-5 w-5" />
                            </div>
                            <div class="space-y-0.5">
                                <p
                                    class="text-xs font-bold tracking-wider text-rose-600/70 uppercase"
                                >
                                    Trashed
                                </p>
                                <p class="text-2xl font-black text-rose-700">
                                    {{ counts.trash_count ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div
                class="flex flex-wrap items-center gap-4 rounded-3xl border border-slate-200/80 bg-white/50 p-4 shadow-sm backdrop-blur-sm dark:border-slate-700 dark:bg-slate-900/50"
            >
                <div class="relative w-full sm:min-w-[240px] sm:flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Search by title, location or ID..."
                        class="h-11 rounded-2xl border-slate-200 bg-white/80 pl-10 transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Select v-if="!presetStatus" v-model="statusFilter">
                        <SelectTrigger
                            class="h-11 w-full rounded-2xl border-slate-200 bg-white/80 sm:w-[160px]"
                        >
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent
                            class="rounded-2xl border-slate-200 shadow-xl"
                        >
                            <SelectItem value="all" class="rounded-lg"
                                >All Statuses</SelectItem
                            >
                            <SelectItem
                                v-for="status in statusOptions as any[]"
                                :key="status.value"
                                :value="status.value"
                                class="rounded-lg"
                            >
                                {{ status.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="guardianFilter">
                        <SelectTrigger
                            class="h-11 w-full rounded-2xl border-slate-200 bg-white/80 sm:w-[180px]"
                        >
                            <SelectValue placeholder="Guardian" />
                        </SelectTrigger>
                        <SelectContent
                            class="rounded-2xl border-slate-200 shadow-xl"
                        >
                            <SelectItem value="all" class="rounded-lg"
                                >All Guardians</SelectItem
                            >
                            <SelectItem
                                v-for="guardian in guardianOptions as any[]"
                                :key="guardian.id"
                                :value="String(guardian.id)"
                                class="rounded-lg"
                            >
                                {{ guardian.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Separator
                        orientation="vertical"
                        class="hidden h-6 sm:block"
                    />

                    <div
                        class="flex overflow-hidden rounded-2xl border border-slate-200 bg-white/80 p-0.5"
                    >
                        <button
                            class="rounded-[13px] px-3 py-1.5 text-xs font-bold transition-all"
                            :class="
                                direction === 'desc'
                                    ? 'bg-slate-900 text-white shadow-sm'
                                    : 'text-slate-500 hover:bg-slate-100'
                            "
                            @click="direction = 'desc'"
                        >
                            DESC
                        </button>
                        <button
                            class="rounded-[13px] px-3 py-1.5 text-xs font-bold transition-all"
                            :class="
                                direction === 'asc'
                                    ? 'bg-slate-900 text-white shadow-sm'
                                    : 'text-slate-500 hover:bg-slate-100'
                            "
                            @click="direction = 'asc'"
                        >
                            ASC
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-xl shadow-slate-200/40 dark:border-slate-700 dark:bg-slate-900 dark:shadow-none"
            >
                <DataTable
                    :items="items"
                    :columns="columns"
                    empty-text="No jobs matching your filters were found."
                    class="border-none"
                    row-class="hover:bg-slate-50/80 transition-colors"
                >
                    <template #cell-title="{ value, row }">
                        <div class="flex flex-col gap-0.5 pb-1">
                            <span
                                class="line-clamp-1 font-bold text-slate-900 transition-colors group-hover:text-blue-600"
                                >{{ value }}</span
                            >
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-[10px] font-bold tracking-tight text-slate-400 uppercase"
                                    >ID: {{ row.id }}</span
                                >
                                <Badge
                                    v-if="row.requested_tutor_id"
                                    variant="outline"
                                    class="h-4 border-blue-200 bg-blue-50 px-1.5 text-[10px] text-blue-700"
                                >
                                    Direct Request
                                </Badge>
                            </div>
                        </div>
                    </template>

                    <template #cell-guardian_name="{ value }">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-slate-100 text-[10px] font-black text-slate-500"
                            >
                                {{ value?.charAt(0) || 'G' }}
                            </div>
                            <span
                                class="text-sm font-semibold text-slate-700"
                                >{{ value }}</span
                            >
                        </div>
                    </template>

                    <template #cell-category_info="{ row }">
                        <div class="space-y-0.5">
                            <p class="text-sm font-bold text-slate-800">
                                {{ row.tuition_type_name || 'Home Tuition' }}
                            </p>
                            <p class="text-xs font-medium text-slate-500">
                                {{ row.category_name }} • {{ row.class_name }}
                            </p>
                        </div>
                    </template>

                    <template #cell-location_info="{ row }">
                        <div class="space-y-0.5">
                            <p class="text-sm font-semibold text-slate-800">
                                {{ row.area_name || 'Location N/A' }}
                            </p>
                            <p class="text-xs font-medium text-slate-500">
                                {{ row.city_name }}
                            </p>
                        </div>
                    </template>

                    <template #cell-applications_count="{ row }">
                        <div class="flex flex-col gap-1">
                            <Link
                                :href="`/admin/jobs/${row.id}/applications`"
                                class="group/link flex items-center gap-1.5"
                            >
                                <span
                                    class="text-sm font-black text-blue-600 group-hover/link:underline"
                                    >{{ row.applications_count ?? 0 }}</span
                                >
                                <Badge
                                    variant="secondary"
                                    class="h-4 shrink-0 border-none bg-blue-50 px-1.5 text-[10px] text-blue-600"
                                >
                                    {{ row.open_applications_count ?? 0 }} open
                                </Badge>
                            </Link>
                            <span
                                class="text-[10px] font-bold tracking-tight text-slate-400 uppercase"
                                >{{ row.view_count ?? 0 }} views</span
                            >
                        </div>
                    </template>

                    <template #cell-hiring_outcome="{ row }">
                        <div v-if="row.has_assignment" class="space-y-1">
                            <div class="flex items-center gap-1.5">
                                <CheckCircle2
                                    class="h-3.5 w-3.5 text-emerald-500"
                                />
                                <span
                                    class="text-sm font-bold text-slate-800"
                                    >{{
                                        row.selected_tutor_name || 'Assigned'
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex flex-col pl-5 text-[10px] text-slate-400"
                            >
                                <span v-if="row.assignment_confirmed_at"
                                    >Finalized:
                                    {{
                                        new Date(
                                            row.assignment_confirmed_at,
                                        ).toLocaleDateString()
                                    }}</span
                                >
                                <span v-else>Matching Phase</span>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex items-center gap-1.5 text-slate-300"
                        >
                            <div
                                class="h-1.5 w-1.5 animate-pulse rounded-full bg-slate-200"
                            ></div>
                            <span
                                class="text-xs font-bold tracking-wider uppercase"
                                >Recruiting</span
                            >
                        </div>
                    </template>

                    <template #cell-status="{ row }">
                        <Badge
                            :variant="statusBadge(row).variant as any"
                            :class="
                                statusBadge(row).class +
                                ' h-6 rounded-lg border px-2.5 text-[11px] font-bold tracking-wide uppercase'
                            "
                        >
                            {{ statusBadge(row).label }}
                        </Badge>
                    </template>

                    <template #cell-dates="{ row }">
                        <div class="flex flex-col gap-1 pr-4">
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <span
                                    class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >Published</span
                                >
                                <span
                                    class="text-[11px] font-bold text-slate-600"
                                    >{{
                                        row.published_at
                                            ? new Date(
                                                  row.published_at,
                                              ).toLocaleDateString()
                                            : 'Draft'
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <span
                                    class="text-[10px] font-black tracking-widest text-slate-400 uppercase"
                                    >Expiry</span
                                >
                                <span
                                    class="text-[11px] font-bold"
                                    :class="
                                        row.is_expired
                                            ? 'text-rose-500'
                                            : 'text-slate-600'
                                    "
                                    >{{
                                        row.expires_at
                                            ? new Date(
                                                  row.expires_at,
                                              ).toLocaleDateString()
                                            : 'N/A'
                                    }}</span
                                >
                            </div>
                        </div>
                    </template>

                    <template #cell-actions="{ row }">
                        <RowActionsDropdown
                            :actions="actionItemsForRow(row)"
                            @select="(action) => handleRowAction(action, row)"
                        />
                    </template>
                </DataTable>
            </div>
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
