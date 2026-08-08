<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import { Badge } from '@/components/ui/badge';
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
    typeOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Finance Invoices', href: '/admin/finance/invoices' },
];
const baseUrl = '/admin/finance/invoices';

const q = ref(props.filters.q || '');
const statusFilter = ref(props.filters.status || 'all');
const typeFilter = ref(props.filters.type || 'all');

watch([q, statusFilter, typeFilter], () => {
    router.get(
        baseUrl,
        {
            q: q.value || '',
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
            type: typeFilter.value === 'all' ? '' : typeFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const columns = [
    { key: 'invoice_no', label: 'Invoice No' },
    { key: 'type', label: 'Type' },
    { key: 'payer', label: 'Payer' },
    { key: 'amount', label: 'Amount' },
    { key: 'status', label: 'Status' },
    { key: 'latest_payment', label: 'Latest Attempt' },
];
</script>

<template>
    <Head title="Finance Invoices" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                >
                    Finance Invoices
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Review verification, service fee, and escrow invoices.
                </p>
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-3 dark:border-slate-800 dark:bg-slate-900"
            >
                <Input
                    v-model="q"
                    placeholder="Search invoice/payer/job..."
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
                <Select v-model="typeFilter">
                    <SelectTrigger
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <SelectValue placeholder="All types" />
                    </SelectTrigger>
                    <SelectContent
                        class="dark:border-slate-800 dark:bg-slate-900"
                    >
                        <SelectItem value="all">All types</SelectItem>
                        <SelectItem
                            v-for="option in typeOptions"
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
                empty-text="No invoices found."
            >
                <template #cell-invoice_no="{ value }">
                    <span
                        class="font-mono text-xs text-slate-700 dark:text-slate-300"
                        >{{ value }}</span
                    >
                </template>

                <template #cell-type="{ value }">
                    <span
                        class="font-medium text-slate-800 capitalize dark:text-slate-200"
                        >{{ value }}</span
                    >
                </template>

                <template #cell-payer="{ row }">
                    <div>
                        <p
                            class="font-medium text-slate-900 dark:text-slate-100"
                        >
                            {{ row.payer.name || '—' }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ row.payer.email || '' }}
                        </p>
                    </div>
                </template>

                <template #cell-amount="{ row }">
                    <span
                        class="font-medium text-slate-800 dark:text-slate-200"
                    >
                        {{ row.currency }} {{ row.amount }}
                    </span>
                </template>

                <template #cell-status="{ value }">
                    <Badge
                        :variant="
                            value === 'paid'
                                ? 'default'
                                : value === 'refunded' || value === 'void'
                                  ? 'destructive'
                                  : 'secondary'
                        "
                    >
                        {{ value }}
                    </Badge>
                </template>

                <template #cell-latest_payment="{ value }">
                    <span v-if="!value" class="text-muted-foreground">—</span>
                    <div
                        v-else
                        class="text-xs text-slate-700 dark:text-slate-300"
                    >
                        <p>{{ value.gateway }} / {{ value.status }}</p>
                        <p class="text-muted-foreground">
                            {{ value.provider_txn_id || '—' }}
                        </p>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
