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
    referenceTypeOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Finance Ledger', href: '/admin/finance/ledger' },
];
const baseUrl = '/admin/finance/ledger';

const q = ref(props.filters.q || '');
const referenceType = ref(props.filters.reference_type || 'all');

watch([q, referenceType], () => {
    router.get(
        baseUrl,
        {
            q: q.value || '',
            reference_type:
                referenceType.value === 'all' ? '' : referenceType.value,
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
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                >
                    Ledger
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Double-entry postings for payments and refunds.
                </p>
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-2 dark:border-slate-800 dark:bg-slate-900"
            >
                <Input
                    v-model="q"
                    placeholder="Search journal/reference/owner..."
                    class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                />
                <Select v-model="referenceType">
                    <SelectTrigger
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <SelectValue placeholder="All references" />
                    </SelectTrigger>
                    <SelectContent
                        class="dark:border-slate-800 dark:bg-slate-900"
                    >
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

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No ledger entries found."
            >
                <template #cell-journal_uuid="{ value }">
                    <span
                        class="font-mono text-xs text-slate-700 dark:text-slate-300"
                        >{{ value }}</span
                    >
                </template>

                <template #cell-type="{ value }">
                    <Badge
                        :variant="value === 'credit' ? 'default' : 'secondary'"
                    >
                        {{ value }}
                    </Badge>
                </template>

                <template #cell-amount="{ row }">
                    <span
                        class="font-medium text-slate-800 dark:text-slate-200"
                    >
                        {{ row.currency }} {{ row.amount }}
                    </span>
                </template>

                <template #cell-owner="{ row }">
                    <span
                        class="font-medium text-slate-800 dark:text-slate-200"
                    >
                        {{ row.owner.name || '—' }}
                    </span>
                </template>

                <template #cell-counterparty="{ row }">
                    <span class="text-slate-700 dark:text-slate-300">
                        {{ row.counterparty.name || '—' }}
                    </span>
                </template>

                <template #cell-reference_type="{ value }">
                    <span class="text-slate-700 dark:text-slate-300">{{
                        value
                    }}</span>
                </template>

                <template #cell-reference_id="{ value }">
                    <span
                        class="font-mono text-xs text-slate-700 dark:text-slate-300"
                        >{{ value }}</span
                    >
                </template>

                <template #cell-is_reversal="{ row }">
                    <Badge
                        :variant="row.is_reversal ? 'destructive' : 'outline'"
                        class="dark:border-slate-700 dark:text-slate-300"
                    >
                        {{ row.is_reversal ? 'Yes' : 'No' }}
                    </Badge>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
