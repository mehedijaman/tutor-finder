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
    totalRequests: number;
    pending: number;
    approved: number;
    rejected: number;
    paid: number;
    totalAmount: number;
    paidAmount: number;
}

interface Summary {
    totalRequests: number;
    totalRequestedAmount: number;
    totalPaidAmount: number;
    approvalRate: number;
}

interface StatusOption {
    value: string;
    label: string;
}

const props = defineProps<{
    reportData: ReportRow[];
    summary: Summary;
    filters: { year: number; month: string; status: string };
    availableYears: number[];
    statusOptions: StatusOption[];
}>();

const breadcrumbs = [
    { title: 'Reports', href: '/admin/reports/income' },
    { title: 'Refund Report', href: '/admin/reports/refunds' },
];

const baseUrl = '/admin/reports/refunds';
const exportUrl = '/admin/reports/refunds/export';

const yearFilter = ref(String(props.filters.year));
const monthFilter = ref(props.filters.month || 'all');
const statusFilter = ref(props.filters.status || 'all');

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

watch([yearFilter, monthFilter, statusFilter], () => {
    router.get(
        baseUrl,
        {
            year: yearFilter.value,
            month: monthFilter.value === 'all' ? '' : monthFilter.value,
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
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
    if (statusFilter.value !== 'all') {
        params.set('status', statusFilter.value);
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
    <Head title="Refund Report" />
    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div
                class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6 print:border-0 print:p-0 print:shadow-none"
            >
                <div>
                    <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">
                        Refund Report
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Track refund requests, approval rates, and payout
                        amounts.
                    </p>
                </div>
                <div class="flex gap-2 print:hidden">
                    <Button variant="outline" size="sm" @click="handlePrint">
                        <Printer class="mr-2 h-4 w-4" />
                        Print
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
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
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-3 print:hidden"
            >
                <Select v-model="yearFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="Select Year" />
                    </SelectTrigger>
                    <SelectContent>
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
                    <SelectTrigger>
                        <SelectValue placeholder="All Months" />
                    </SelectTrigger>
                    <SelectContent>
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
                <Select v-model="statusFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All Statuses" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Statuses</SelectItem>
                        <SelectItem
                            v-for="opt in statusOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Print Header -->
            <div class="hidden print:mb-4 print:block">
                <h2 class="text-lg font-semibold">
                    Refund Report — {{ yearFilter }}
                </h2>
                <p class="text-sm text-gray-500">
                    Generated on {{ new Date().toLocaleDateString() }}
                </p>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Requests
                    </p>
                    <p class="mt-1 text-2xl font-bold">
                        {{ summary.totalRequests }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Requested
                    </p>
                    <p class="mt-1 text-2xl font-bold text-amber-600">
                        BDT {{ formatCurrency(summary.totalRequestedAmount) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Paid Out
                    </p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">
                        BDT {{ formatCurrency(summary.totalPaidAmount) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Approval Rate
                    </p>
                    <p class="mt-1 text-2xl font-bold text-indigo-600">
                        {{ summary.approvalRate }}%
                    </p>
                </div>
            </div>

            <!-- Data Table -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm print:rounded-none print:shadow-none"
            >
                <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 print:bg-gray-100">
                        <tr>
                            <th
                                class="px-5 py-4 text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Month
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Total
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Pending
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Approved
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Rejected
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Paid
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Amount (BDT)
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Paid (BDT)
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in reportData"
                            :key="row.month"
                            class="border-t border-slate-100 transition-colors hover:bg-slate-50/80 print:hover:bg-transparent"
                        >
                            <td class="px-5 py-4 font-medium text-slate-700">
                                {{ row.label }}
                            </td>
                            <td class="px-5 py-4 text-right font-semibold">
                                {{ row.totalRequests }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Badge
                                    v-if="row.pending > 0"
                                    variant="secondary"
                                    >{{ row.pending }}</Badge
                                >
                                <span v-else class="text-muted-foreground"
                                    >0</span
                                >
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Badge
                                    v-if="row.approved > 0"
                                    variant="default"
                                    >{{ row.approved }}</Badge
                                >
                                <span v-else class="text-muted-foreground"
                                    >0</span
                                >
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Badge
                                    v-if="row.rejected > 0"
                                    variant="destructive"
                                    >{{ row.rejected }}</Badge
                                >
                                <span v-else class="text-muted-foreground"
                                    >0</span
                                >
                            </td>
                            <td class="px-5 py-4 text-right">
                                <Badge v-if="row.paid > 0" variant="default">{{
                                    row.paid
                                }}</Badge>
                                <span v-else class="text-muted-foreground"
                                    >0</span
                                >
                            </td>
                            <td class="px-5 py-4 text-right text-slate-600">
                                {{ formatCurrency(row.totalAmount) }}
                            </td>
                            <td
                                class="px-5 py-4 text-right font-medium text-emerald-600"
                            >
                                {{ formatCurrency(row.paidAmount) }}
                            </td>
                        </tr>
                        <tr v-if="reportData.length === 0" class="border-t">
                            <td
                                colspan="8"
                                class="px-5 py-8 text-center text-muted-foreground"
                            >
                                No refund data found for the selected period.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot
                        class="border-t-2 border-slate-300 bg-slate-50 font-semibold print:bg-gray-100"
                    >
                        <tr>
                            <td class="px-5 py-4 text-slate-900">
                                Grand Total
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{ summary.totalRequests }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{
                                    reportData.reduce(
                                        (s, r) => s + r.pending,
                                        0,
                                    )
                                }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{
                                    reportData.reduce(
                                        (s, r) => s + r.approved,
                                        0,
                                    )
                                }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{
                                    reportData.reduce(
                                        (s, r) => s + r.rejected,
                                        0,
                                    )
                                }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{ reportData.reduce((s, r) => s + r.paid, 0) }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{
                                    formatCurrency(summary.totalRequestedAmount)
                                }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{ formatCurrency(summary.totalPaidAmount) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
