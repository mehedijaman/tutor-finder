<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
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
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Refund Requests', href: '/admin/finance/refund-requests' },
];
const baseUrl = '/admin/finance/refund-requests';

const q = ref(props.filters.q || '');
const statusFilter = ref(props.filters.status || 'all');

watch([q, statusFilter], () => {
    router.get(
        baseUrl,
        {
            q: q.value || '',
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const columns = [
    { key: 'id', label: '#' },
    { key: 'job', label: 'Job' },
    { key: 'requester', label: 'Tutor' },
    { key: 'amount', label: 'Amount' },
    { key: 'status', label: 'Status' },
    { key: 'requested_at', label: 'Requested' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

function approve(row) {
    router.patch(
        `${baseUrl}/${row.id}/decision`,
        {
            status: 'approved',
            decision_note: 'Approved by admin.',
        },
        { preserveScroll: true },
    );
}

function reject(row) {
    const reason = window.prompt('Rejection reason', 'Rejected by admin.');

    if (reason === null) {
        return;
    }

    router.patch(
        `${baseUrl}/${row.id}/decision`,
        {
            status: 'rejected',
            decision_note: reason,
        },
        { preserveScroll: true },
    );
}

function markPaid(row) {
    router.patch(
        `${baseUrl}/${row.id}/mark-paid`,
        {
            gateway: 'manual',
            provider_txn_id: `REFUND-${row.id}-${Date.now()}`,
            note: 'Refund payout recorded manually.',
        },
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Refund Requests" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                >
                    Refund Requests
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Review tutor refund requests and process payout decisions.
                </p>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                v-if="$page.props.errors?.refund"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
            >
                {{ $page.props.errors.refund }}
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-2 dark:border-slate-800 dark:bg-slate-900"
            >
                <Input
                    v-model="q"
                    placeholder="Search reason/job/tutor..."
                    class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                />
                <Select v-model="statusFilter">
                    <SelectTrigger
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <SelectValue placeholder="All statuses" />
                    </SelectTrigger>
                    <SelectContent
                        class="dark:border-slate-800 dark:bg-slate-900"
                    >
                        <SelectItem value="all">All statuses</SelectItem>
                        <SelectItem
                            v-for="option in statusOptions"
                            :key="option"
                            :value="option"
                        >
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No refund requests found."
            >
                <template #cell-id="{ value }">
                    <span
                        class="font-mono text-xs text-slate-700 dark:text-slate-300"
                        >{{ value }}</span
                    >
                </template>
                <template #cell-job="{ row }">
                    <span
                        class="font-medium text-slate-900 dark:text-slate-100"
                        >{{ row.job.title || '—' }}</span
                    >
                </template>
                <template #cell-requester="{ row }">
                    <span class="text-slate-800 dark:text-slate-200">{{
                        row.requester.name || '—'
                    }}</span>
                </template>
                <template #cell-amount="{ row }">
                    <span class="font-medium text-slate-800 dark:text-slate-200"
                        >{{ row.currency }} {{ row.amount }}</span
                    >
                </template>
                <template #cell-status="{ value }">
                    <Badge
                        :variant="
                            value === 'paid'
                                ? 'default'
                                : value === 'rejected'
                                  ? 'destructive'
                                  : 'secondary'
                        "
                    >
                        {{ value }}
                    </Badge>
                </template>
                <template #cell-requested_at="{ value }">
                    <span class="text-slate-700 dark:text-slate-300">
                        {{ value ? new Date(value).toLocaleString() : '—' }}
                    </span>
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-2">
                        <Button
                            v-if="row.status === 'pending'"
                            size="sm"
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-300"
                            @click="approve(row)"
                        >
                            Approve
                        </Button>
                        <Button
                            v-if="row.status === 'pending'"
                            size="sm"
                            variant="destructive"
                            @click="reject(row)"
                        >
                            Reject
                        </Button>
                        <Button
                            v-if="row.status === 'approved'"
                            size="sm"
                            @click="markPaid(row)"
                        >
                            Mark Paid
                        </Button>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
