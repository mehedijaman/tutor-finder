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
    gatewayOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Finance Payments', href: '/admin/finance/payments' },
];
const baseUrl = '/admin/finance/payments';

const q = ref(props.filters.q || '');
const statusFilter = ref(props.filters.status || 'all');
const gatewayFilter = ref(props.filters.gateway || 'all');

watch([q, statusFilter, gatewayFilter], () => {
    router.get(
        baseUrl,
        {
            q: q.value || '',
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
            gateway: gatewayFilter.value === 'all' ? '' : gatewayFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const columns = [
    { key: 'id', label: '#' },
    { key: 'gateway', label: 'Gateway' },
    { key: 'provider_txn_id', label: 'Provider Txn' },
    { key: 'amount', label: 'Amount' },
    { key: 'status', label: 'Status' },
    { key: 'invoice', label: 'Invoice' },
];
</script>

<template>
    <Head title="Finance Payments" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                    Payment Attempts
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Monitor gateway attempts and reconciliation status.
                </p>
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-3"
            >
                <Input v-model="q" placeholder="Search txn/invoice/payer..." />
                <Select v-model="statusFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All statuses" />
                    </SelectTrigger>
                    <SelectContent>
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
                <Select v-model="gatewayFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All gateways" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All gateways</SelectItem>
                        <SelectItem
                            v-for="option in gatewayOptions"
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
                empty-text="No payment attempts found."
            >
                <template #cell-amount="{ row }">{{ row.amount }}</template>
                <template #cell-status="{ value }">
                    <Badge
                        :variant="
                            value === 'paid'
                                ? 'default'
                                : value === 'failed' || value === 'cancelled'
                                  ? 'destructive'
                                  : 'secondary'
                        "
                    >
                        {{ value }}
                    </Badge>
                </template>
                <template #cell-invoice="{ row }">
                    <div>
                        <p class="font-medium">
                            {{ row.invoice.invoice_no || '—' }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ row.invoice.payer_name || '' }}
                        </p>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
