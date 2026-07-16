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
    totalJobs: number;
    pending: number;
    live: number;
    confirmed: number;
    cancelled: number;
    closed: number;
    assignments: number;
    totalSalary: number;
    avgSalary: number;
}

interface Summary {
    totalJobs: number;
    totalAssignments: number;
    totalSalary: number;
    avgSalary: number;
    confirmationRate: number;
}

const props = defineProps<{
    reportData: ReportRow[];
    summary: Summary;
    filters: { year: number; month: string };
    availableYears: number[];
}>();

const breadcrumbs = [
    { title: 'Reports', href: '/admin/reports/income' },
    { title: 'Tuition Report', href: '/admin/reports/tuition' },
];

const baseUrl = '/admin/reports/tuition';
const exportUrl = '/admin/reports/tuition/export';

const yearFilter = ref(String(props.filters.year));
const monthFilter = ref(props.filters.month || 'all');

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

watch([yearFilter, monthFilter], () => {
    router.get(
        baseUrl,
        {
            year: yearFilter.value,
            month: monthFilter.value === 'all' ? '' : monthFilter.value,
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
    <Head title="Tuition Report" />
    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div
                class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6 print:border-0 print:p-0 print:shadow-none"
            >
                <div>
                    <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">
                        Monthly Tuition Report
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Tuition jobs overview with status breakdown,
                        assignments, and salary data.
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
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-2 print:hidden"
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
            </div>

            <!-- Print Header -->
            <div class="hidden print:mb-4 print:block">
                <h2 class="text-lg font-semibold">
                    Tuition Report — {{ yearFilter }}
                </h2>
                <p class="text-sm text-gray-500">
                    Generated on {{ new Date().toLocaleDateString() }}
                </p>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Jobs
                    </p>
                    <p class="mt-1 text-2xl font-bold">
                        {{ summary.totalJobs }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Assignments
                    </p>
                    <p class="mt-1 text-2xl font-bold text-blue-600">
                        {{ summary.totalAssignments }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Salary
                    </p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">
                        BDT {{ formatCurrency(summary.totalSalary) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Avg Salary
                    </p>
                    <p class="mt-1 text-2xl font-bold">
                        BDT {{ formatCurrency(summary.avgSalary) }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Confirmation Rate
                    </p>
                    <p class="mt-1 text-2xl font-bold text-indigo-600">
                        {{ summary.confirmationRate }}%
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
                                    Live
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                                >
                                    Confirmed
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                                >
                                    Cancelled
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                                >
                                    Closed
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                                >
                                    Assigned
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                                >
                                    Salary (BDT)
                                </th>
                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                                >
                                    Avg Salary
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in reportData"
                                :key="row.month"
                                class="border-t border-slate-100 transition-colors hover:bg-slate-50/80 print:hover:bg-transparent"
                            >
                                <td
                                    class="px-5 py-4 font-medium text-slate-700"
                                >
                                    {{ row.label }}
                                </td>
                                <td class="px-5 py-4 text-right font-semibold">
                                    {{ row.totalJobs }}
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
                                        v-if="row.live > 0"
                                        variant="default"
                                        >{{ row.live }}</Badge
                                    >
                                    <span v-else class="text-muted-foreground"
                                        >0</span
                                    >
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <Badge
                                        v-if="row.confirmed > 0"
                                        variant="default"
                                        >{{ row.confirmed }}</Badge
                                    >
                                    <span v-else class="text-muted-foreground"
                                        >0</span
                                    >
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <Badge
                                        v-if="row.cancelled > 0"
                                        variant="destructive"
                                        >{{ row.cancelled }}</Badge
                                    >
                                    <span v-else class="text-muted-foreground"
                                        >0</span
                                    >
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span>{{ row.closed }}</span>
                                </td>
                                <td
                                    class="px-5 py-4 text-right font-medium text-blue-600"
                                >
                                    {{ row.assignments }}
                                </td>
                                <td class="px-5 py-4 text-right text-slate-600">
                                    {{ formatCurrency(row.totalSalary) }}
                                </td>
                                <td class="px-5 py-4 text-right text-slate-600">
                                    {{ formatCurrency(row.avgSalary) }}
                                </td>
                            </tr>
                            <tr v-if="reportData.length === 0" class="border-t">
                                <td
                                    colspan="10"
                                    class="px-5 py-8 text-center text-muted-foreground"
                                >
                                    No tuition data found for the selected
                                    period.
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
                                    {{ summary.totalJobs }}
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
                                            (s, r) => s + r.live,
                                            0,
                                        )
                                    }}
                                </td>
                                <td class="px-5 py-4 text-right text-slate-900">
                                    {{
                                        reportData.reduce(
                                            (s, r) => s + r.confirmed,
                                            0,
                                        )
                                    }}
                                </td>
                                <td class="px-5 py-4 text-right text-slate-900">
                                    {{
                                        reportData.reduce(
                                            (s, r) => s + r.cancelled,
                                            0,
                                        )
                                    }}
                                </td>
                                <td class="px-5 py-4 text-right text-slate-900">
                                    {{
                                        reportData.reduce(
                                            (s, r) => s + r.closed,
                                            0,
                                        )
                                    }}
                                </td>
                                <td class="px-5 py-4 text-right text-slate-900">
                                    {{ summary.totalAssignments }}
                                </td>
                                <td class="px-5 py-4 text-right text-slate-900">
                                    {{ formatCurrency(summary.totalSalary) }}
                                </td>
                                <td class="px-5 py-4 text-right text-slate-900">
                                    {{ formatCurrency(summary.avgSalary) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
