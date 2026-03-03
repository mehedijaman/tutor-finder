<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
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

function pay(invoiceId, gateway) {
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
        <div class="space-y-4 p-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold">Payments & Escrow</h1>
                <p class="text-sm text-muted-foreground">
                    Pay your verification or escrow invoices from one place.
                </p>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                v-if="$page.props.errors?.payment"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
            >
                {{ $page.props.errors.payment }}
            </div>

            <div class="max-w-xs">
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
                        {{ value }}
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
