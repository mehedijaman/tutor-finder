<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';
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
import TutorLayout from '@/layouts/TutorLayout.vue';

const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
    eligibleAssignments: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Refund Requests', href: '/tutor/finance/refunds' },
];
const baseUrl = '/tutor/finance/refunds';

const statusFilter = ref(props.filters.status || 'all');
const form = reactive({
    assignment_id: '',
    reason_text: '',
});

watch(statusFilter, () => {
    router.get(
        baseUrl,
        {
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const columns = [
    { key: 'job', label: 'Job' },
    { key: 'amount', label: 'Amount' },
    { key: 'status', label: 'Status' },
    { key: 'requested_at', label: 'Requested At' },
    { key: 'decision_note', label: 'Decision' },
];

function submitRefund() {
    if (!form.assignment_id || !form.reason_text) {
        return;
    }

    router.post(
        `${baseUrl}/${form.assignment_id}`,
        { reason_text: form.reason_text },
        {
            preserveScroll: true,
            onSuccess: () => {
                form.reason_text = '';
            },
        },
    );
}
</script>

<template>
    <Head title="Refund Requests" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                    Refund Requests
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Submit and track service fee refund requests.
                </p>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                v-if="$page.props.errors?.refund"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
            >
                {{ $page.props.errors.refund }}
            </div>

            <section
                class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
            >
                <h2 class="text-base font-semibold">Create Refund Request</h2>
                <div class="grid gap-3 md:grid-cols-2">
                    <Select v-model="form.assignment_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Select assignment" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="assignment in eligibleAssignments"
                                :key="assignment.id"
                                :value="String(assignment.id)"
                            >
                                {{ assignment.job_title }} ({{
                                    assignment.currency
                                }}
                                {{ assignment.service_fee_amount }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Input
                        :model-value="form.assignment_id"
                        placeholder="Assignment ID"
                        readonly
                    />
                </div>
                <textarea
                    v-model="form.reason_text"
                    rows="4"
                    class="w-full rounded-lg border border-slate-300 bg-background px-3 py-2 text-sm"
                    placeholder="Explain why you are requesting a refund..."
                />
                <Button
                    type="button"
                    :disabled="!form.assignment_id || !form.reason_text"
                    @click="submitRefund"
                >
                    Submit Request
                </Button>
            </section>

            <section class="space-y-3">
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                >
                    <div class="max-w-xs">
                        <Select v-model="statusFilter">
                            <SelectTrigger>
                                <SelectValue placeholder="All statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all"
                                    >All statuses</SelectItem
                                >
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
                </div>

                <DataTable
                    :items="items"
                    :columns="columns"
                    empty-text="No refund requests found."
                >
                    <template #cell-job="{ row }">{{
                        row.job.title || '—'
                    }}</template>
                    <template #cell-amount="{ row }"
                        >{{ row.currency }} {{ row.amount }}</template
                    >
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
                        {{ value ? new Date(value).toLocaleString() : '—' }}
                    </template>
                </DataTable>
            </section>
        </div>
    </TutorLayout>
</template>
