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
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                >
                    Refund Requests
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Submit and track service fee refund requests.
                </p>
            </div>

            <div
                v-if="($page.props.flash as any)?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
            >
                {{ ($page.props.flash as any).status }}
            </div>

            <div
                v-if="($page.props.errors as any)?.refund"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
            >
                {{ ($page.props.errors as any).refund }}
            </div>

            <section
                class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <h2
                    class="text-base font-semibold text-slate-900 dark:text-slate-100"
                >
                    Create Refund Request
                </h2>
                <div class="grid gap-3 md:grid-cols-2">
                    <Select v-model="form.assignment_id">
                        <SelectTrigger
                            class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <SelectValue placeholder="Select assignment" />
                        </SelectTrigger>
                        <SelectContent
                            class="dark:border-slate-700 dark:bg-slate-900"
                        >
                            <SelectItem
                                v-for="assignment in eligibleAssignments as any[]"
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
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    />
                </div>
                <textarea
                    v-model="form.reason_text"
                    rows="4"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500"
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
                    class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="max-w-xs">
                        <Select v-model="statusFilter">
                            <SelectTrigger
                                class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            >
                                <SelectValue placeholder="All statuses" />
                            </SelectTrigger>
                            <SelectContent
                                class="dark:border-slate-700 dark:bg-slate-900"
                            >
                                <SelectItem value="all"
                                    >All statuses</SelectItem
                                >
                                <SelectItem
                                    v-for="option in statusOptions as string[]"
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
