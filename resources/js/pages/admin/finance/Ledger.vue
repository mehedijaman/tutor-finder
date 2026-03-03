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
    referenceTypeOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [{ title: 'Finance Ledger', href: '/admin/finance/ledger' }];
const baseUrl = '/admin/finance/ledger';

const q = ref(props.filters.q || '');
const referenceType = ref(props.filters.reference_type || 'all');

watch([q, referenceType], () => {
    router.get(
        baseUrl,
        {
            q: q.value || '',
            reference_type: referenceType.value === 'all' ? '' : referenceType.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const columns = [
    { key: 'journal_uuid', label: 'Journal' },
    { key: 'type', label: 'Type' },
    { key: 'amount', label: 'Amount' },
    { key: 'owner', label: 'Owner' },
    { key: 'counterparty', label: 'Counterparty' },
    { key: 'reference_type', label: 'Ref Type' },
    { key: 'reference_id', label: 'Ref ID' },
    { key: 'is_reversal', label: 'Reversal' },
];
</script>

<template>
    <Head title="Finance Ledger" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-6">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold">Ledger</h1>
                <p class="text-sm text-muted-foreground">
                    Double-entry postings for payments and refunds.
                </p>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <Input v-model="q" placeholder="Search journal/reference/owner..." />
                <Select v-model="referenceType">
                    <SelectTrigger>
                        <SelectValue placeholder="All references" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All references</SelectItem>
                        <SelectItem
                            v-for="option in referenceTypeOptions"
                            :key="option"
                            :value="option"
                        >
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DataTable :items="items" :columns="columns" empty-text="No ledger entries found.">
                <template #cell-amount="{ row }">{{ row.currency }} {{ row.amount }}</template>
                <template #cell-owner="{ row }">{{ row.owner.name || '—' }}</template>
                <template #cell-counterparty="{ row }">{{ row.counterparty.name || '—' }}</template>
                <template #cell-type="{ value }">
                    <Badge :variant="value === 'credit' ? 'default' : 'secondary'">
                        {{ value }}
                    </Badge>
                </template>
                <template #cell-is_reversal="{ row }">
                    <Badge :variant="row.is_reversal ? 'destructive' : 'outline'">
                        {{ row.is_reversal ? 'Yes' : 'No' }}
                    </Badge>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
