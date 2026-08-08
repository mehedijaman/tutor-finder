<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import GuardianLayout from '@/layouts/GuardianLayout.vue';

const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Payments & Escrow', href: '/guardian/finance/invoices' },
];
const baseUrl = '/guardian/finance/invoices';
const page = usePage();
const flashStatus = computed<string | null>(
    () => (page.props.flash as { status?: string } | undefined)?.status ?? null,
);
const statusOptionsList = computed<string[]>(
    () => (props.statusOptions as string[] | undefined) ?? [],
);

const statusFilter = ref(props.filters.status || 'all');

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
    { key: 'invoice_no', label: 'Invoice No' },
    { key: 'type', label: 'Type' },
    { key: 'amount', label: 'Amount' },
    { key: 'status', label: 'Status' },
    { key: 'due_at', label: 'Due' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

function pay(invoiceId: number | string, gateway: 'bkash' | 'sslcommerz') {
    const endpoint =
        gateway === 'bkash'
            ? `/payment/bkash/${invoiceId}`
            : `/payment/sslcommerz/${invoiceId}`;

    router.post(endpoint, {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Payments & Escrow" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                >
                    Payments & Escrow
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Pay your verification or escrow invoices from one place.
                </p>
            </div>

            <div
                v-if="flashStatus"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
            >
                {{ flashStatus }}
            </div>

            <div
                v-if="($page.props.errors as any)?.payment"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
            >
                {{ ($page.props.errors as any).payment }}
            </div>

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
                            <SelectItem value="all">All statuses</SelectItem>
                            <SelectItem
                                v-for="option in statusOptionsList"
                                :key="option"
                                :value="option"
                            >
                                {{
                                    option
                                        .replace('_', ' ')
                                        .split(' ')
                                        .map(
                                            (w) =>
                                                w.charAt(0).toUpperCase() +
                                                w.slice(1),
                                        )
                                        .join(' ')
                                }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No invoices found."
            >
                <template #cell-amount="{ row }"
                    >{{ row.currency }} {{ row.amount }}</template
                >
                <template #cell-status="{ value }">
                    <Badge
                        :variant="
                            value === 'paid'
                                ? 'default'
                                : value === 'void' || value === 'refunded'
                                  ? 'destructive'
                                  : 'secondary'
                        "
                    >
                        {{
                            value
                                .replace('_', ' ')
                                .split(' ')
                                .map(
                                    (w: string) =>
                                        w.charAt(0).toUpperCase() + w.slice(1),
                                )
                                .join(' ')
                        }}
                    </Badge>
                </template>
                <template #cell-due_at="{ value }">
                    {{ value ? new Date(value).toLocaleString() : '—' }}
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex gap-2">
                        <Button
                            size="sm"
                            :disabled="row.status !== 'unpaid'"
                            @click="pay(row.id, 'bkash')"
                        >
                            bKash
                        </Button>
                        <Button
                            size="sm"
                            variant="outline"
                            class="dark:border-slate-700 dark:text-slate-200"
                            :disabled="row.status !== 'unpaid'"
                            @click="pay(row.id, 'sslcommerz')"
                        >
                            SSL
                        </Button>
                    </div>
                </template>
            </DataTable>
        </div>
    </GuardianLayout>
</template>
