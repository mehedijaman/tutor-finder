<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Download, Printer } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface ReportRow {
    month: number;
    label: string;
    serviceFee: number;
    verificationFee: number;
    escrow: number;
    total: number;
    paidCount: number;
    unpaidCount: number;
}

interface Summary {
    totalIncome: number;
    totalPaid: number;
    totalUnpaid: number;
    totalInvoices: number;
    currency: string;
}

interface TypeOption {
    value: string;
    label: string;
}

const props = defineProps<{
    reportData: ReportRow[];
    summary: Summary;
    filters: { year: number; month: string; type: string };
    availableYears: number[];
    typeOptions: TypeOption[];
}>();

const breadcrumbs = [
    { title: 'Reports', href: '/admin/reports/income' },
    { title: 'Income Report', href: '/admin/reports/income' },
];

const baseUrl = '/admin/reports/income';
const exportUrl = '/admin/reports/income/export';

const yearFilter = ref(String(props.filters.year));
const monthFilter = ref(props.filters.month || 'all');
const typeFilter = ref(props.filters.type || 'all');

const months = [
    { value: '1', label: 'January' },
    { value: '2', label: 'February' },
    { value: '3', label: 'March' },
    { value: '4', label: 'April' },
    { value: '5', label: 'May' },
    { value: '6', label: 'June' },
    { value: '7', label: 'July' },
    { value: '8', label: 'August' },
    { value: '9', label: 'September' },
    { value: '10', label: 'October' },
    { value: '11', label: 'November' },
    { value: '12', label: 'December' },
];

watch([yearFilter, monthFilter, typeFilter], () => {
    router.get(
        baseUrl,
        {
            year: yearFilter.value,
            month: monthFilter.value === 'all' ? '' : monthFilter.value,
            type: typeFilter.value === 'all' ? '' : typeFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const exportHref = computed(() => {
    const params = new URLSearchParams();
    params.set('year', yearFilter.value);
    if (monthFilter.value !== 'all') {
        params.set('month', monthFilter.value);
    }
    if (typeFilter.value !== 'all') {
        params.set('type', typeFilter.value);
    }
    return `${exportUrl}?${params.toString()}`;
});

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

function handlePrint(): void {
    window.print();
}
</script>

<template>
    <Head title="Income Report" />
    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div
                class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6 dark:border-slate-800 dark:bg-slate-900 print:border-0 print:p-0 print:shadow-none"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100"
                    >
                        Monthly Income Report
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Revenue breakdown by service fees, verification fees,
                        and escrow payments.
                    </p>
                </div>
                <div class="flex gap-2 print:hidden">
                    <Button
                        variant="outline"
                        size="sm"
                        class="dark:border-slate-700 dark:text-slate-300"
                        @click="handlePrint"
                    >
                        <Printer class="mr-2 h-4 w-4" />
                        Print
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        class="dark:border-slate-700 dark:text-slate-300"
                        as="a"
                        :href="exportHref"
                    >
                        <Download class="mr-2 h-4 w-4" />
                        Export CSV
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-3 dark:border-slate-800 dark:bg-slate-900 print:hidden"
            >
                <Select v-model="yearFilter">
                    <SelectTrigger
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <SelectValue placeholder="Select Year" />
                    </SelectTrigger>
                    <SelectContent
                        class="dark:border-slate-800 dark:bg-slate-900"
                    >
                        <SelectItem
                            v-for="y in availableYears"
                            :key="y"
                            :value="String(y)"
                        >
                            {{ y }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="monthFilter">
                    <SelectTrigger
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <SelectValue placeholder="All Months" />
                    </SelectTrigger>
                    <SelectContent
                        class="dark:border-slate-800 dark:bg-slate-900"
                    >
                        <SelectItem value="all">All Months</SelectItem>
                        <SelectItem
                            v-for="m in months"
                            :key="m.value"
                            :value="m.value"
                        >
                            {{ m.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="typeFilter">
                    <SelectTrigger
                        class="dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <SelectValue placeholder="All Types" />
                    </SelectTrigger>
                    <SelectContent
                        class="dark:border-slate-800 dark:bg-slate-900"
                    >
                        <SelectItem value="all">All Types</SelectItem>
                        <SelectItem
                            v-for="opt in typeOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Print Header (visible only when printing) -->
            <div class="hidden print:mb-4 print:block">
                <h2 class="text-lg font-semibold">
                    Income Report — {{ yearFilter }}
                </h2>
                <p class="text-sm text-gray-500">
                    Generated on {{ new Date().toLocaleDateString() }}
                </p>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Income
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400"
                    >
                        {{ summary.currency }}
                        {{ formatCurrency(summary.totalIncome) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Paid
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400"
                    >
                        {{ summary.currency }}
                        {{ formatCurrency(summary.totalPaid) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Unpaid
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400"
                    >
                        {{ summary.currency }}
                        {{ formatCurrency(summary.totalUnpaid) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Invoices
                    </p>
                    <p
                        class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100"
                    >
                        {{ summary.totalInvoices }}
                    </p>
                </div>
            </div>

            <!-- Data Table -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 print:rounded-none print:shadow-none"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="bg-slate-50 dark:bg-slate-800 print:bg-gray-100"
                        >
                            <tr>
                                <th
                                    class="px-5 py-4 text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >
                                    Month
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >
                                    Service Fee
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >
                                    Verification Fee
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >
                                    Escrow
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >
                                    Total
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >
                                    Paid
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >
                                    Unpaid
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="row in reportData"
                                :key="row.month"
                                class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/50 print:hover:bg-transparent"
                            >
                                <td
                                    class="px-5 py-4 font-medium text-slate-700 dark:text-slate-300"
                                >
                                    {{ row.label }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right text-slate-600 dark:text-slate-400"
                                >
                                    {{ formatCurrency(row.serviceFee) }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right text-slate-600 dark:text-slate-400"
                                >
                                    {{ formatCurrency(row.verificationFee) }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right text-slate-600 dark:text-slate-400"
                                >
                                    {{ formatCurrency(row.escrow) }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right font-semibold text-slate-900 dark:text-slate-100"
                                >
                                    {{ formatCurrency(row.total) }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <Badge variant="default">{{
                                        row.paidCount
                                    }}</Badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <Badge
                                        v-if="row.unpaidCount > 0"
                                        variant="secondary"
                                        >{{ row.unpaidCount }}</Badge
                                    >
                                    <span v-else class="text-muted-foreground"
                                        >0</span
                                    >
                                </td>
                            </tr>
                            <tr v-if="reportData.length === 0">
                                <td
                                    colspan="7"
                                    class="px-5 py-8 text-center text-muted-foreground"
                                >
                                    No income data found for the selected
                                    period.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot
                            class="border-t-2 border-slate-300 bg-slate-50 font-semibold dark:border-slate-700 dark:bg-slate-800 print:bg-gray-100"
                        >
                            <tr>
                                <td
                                    class="px-5 py-4 text-slate-900 dark:text-slate-100"
                                >
                                    Grand Total
                                </td>
                                <td
                                    class="px-5 py-4 text-right text-slate-900 dark:text-slate-100"
                                >
                                    {{
                                        formatCurrency(
                                            reportData.reduce(
                                                (s, r) => s + r.serviceFee,
                                                0,
                                            ),
                                        )
                                    }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right text-slate-900 dark:text-slate-100"
                                >
                                    {{
                                        formatCurrency(
                                            reportData.reduce(
                                                (s, r) => s + r.verificationFee,
                                                0,
                                            ),
                                        )
                                    }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right text-slate-900 dark:text-slate-100"
                                >
                                    {{
                                        formatCurrency(
                                            reportData.reduce(
                                                (s, r) => s + r.escrow,
                                                0,
                                            ),
                                        )
                                    }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right text-slate-900 dark:text-slate-100"
                                >
                                    {{ formatCurrency(summary.totalIncome) }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right text-slate-900 dark:text-slate-100"
                                >
                                    {{
                                        reportData.reduce(
                                            (s, r) => s + r.paidCount,
                                            0,
                                        )
                                    }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right text-slate-900 dark:text-slate-100"
                                >
                                    {{
                                        reportData.reduce(
                                            (s, r) => s + r.unpaidCount,
                                            0,
                                        )
                                    }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
