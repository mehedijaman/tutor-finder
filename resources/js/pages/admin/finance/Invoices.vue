<script setup>
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

const breadcrumbs = [{ title: 'Finance Invoices', href: '/admin/finance/invoices' }];
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
        <div class="space-y-4 p-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold">Finance Invoices</h1>
                <p class="text-sm text-muted-foreground">
                    Review verification, service fee, and escrow invoices.
                </p>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <Input v-model="q" placeholder="Search invoice/payer/job..." />
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
                <Select v-model="typeFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All types" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All types</SelectItem>
                        <SelectItem v-for="option in typeOptions" :key="option" :value="option">
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DataTable :items="items" :columns="columns" empty-text="No invoices found.">
                <template #cell-payer="{ row }">
                    <div>
                        <p class="font-medium">{{ row.payer.name || '—' }}</p>
                        <p class="text-xs text-muted-foreground">{{ row.payer.email || '' }}</p>
                    </div>
                </template>
                <template #cell-amount="{ row }">
                    {{ row.currency }} {{ row.amount }}
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
                    <div v-else class="text-xs">
                        <p>{{ value.gateway }} / {{ value.status }}</p>
                        <p class="text-muted-foreground">{{ value.provider_txn_id || '—' }}</p>
                    </div>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
