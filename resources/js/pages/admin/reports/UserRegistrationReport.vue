<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Download, Printer } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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
    newTutors: number;
    newGuardians: number;
    totalRegistrations: number;
    activeTutors: number;
    activeGuardians: number;
    verifiedTutors: number;
    verifiedGuardians: number;
}

interface Summary {
    totalRegistrations: number;
    totalTutors: number;
    totalGuardians: number;
    totalActive: number;
    totalVerified: number;
}

const props = defineProps<{
    reportData: ReportRow[];
    summary: Summary;
    filters: { year: number; month: string; role: string };
    availableYears: number[];
}>();

const breadcrumbs = [
    { title: 'Reports', href: '/admin/reports/income' },
    { title: 'User Registrations', href: '/admin/reports/user-registrations' },
];

const baseUrl = '/admin/reports/user-registrations';
const exportUrl = '/admin/reports/user-registrations/export';

const yearFilter = ref(String(props.filters.year));
const monthFilter = ref(props.filters.month || 'all');
const roleFilter = ref(props.filters.role || 'all');

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

watch([yearFilter, monthFilter, roleFilter], () => {
    router.get(
        baseUrl,
        {
            year: yearFilter.value,
            month: monthFilter.value === 'all' ? '' : monthFilter.value,
            role: roleFilter.value === 'all' ? '' : roleFilter.value,
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
    if (roleFilter.value !== 'all') {
        params.set('role', roleFilter.value);
    }
    return `${exportUrl}?${params.toString()}`;
});

function handlePrint(): void {
    window.print();
}
</script>

<template>
    <Head title="User Registration Report" />
    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div
                class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6 print:border-0 print:p-0 print:shadow-none"
            >
                <div>
                    <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">
                        User Registration Report
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Monitor tutor and guardian registration trends over
                        time.
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
                <Select v-model="roleFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All Roles" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Roles</SelectItem>
                        <SelectItem value="tutor">Tutors</SelectItem>
                        <SelectItem value="guardian">Guardians</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Print Header -->
            <div class="hidden print:mb-4 print:block">
                <h2 class="text-lg font-semibold">
                    User Registration Report — {{ yearFilter }}
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
                        Total Registrations
                    </p>
                    <p class="mt-1 text-2xl font-bold">
                        {{ summary.totalRegistrations }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Tutors
                    </p>
                    <p class="mt-1 text-2xl font-bold text-blue-600">
                        {{ summary.totalTutors }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Guardians
                    </p>
                    <p class="mt-1 text-2xl font-bold text-purple-600">
                        {{ summary.totalGuardians }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Active Users
                    </p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">
                        {{ summary.totalActive }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm print:rounded-none print:border print:shadow-none"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Verified Users
                    </p>
                    <p class="mt-1 text-2xl font-bold text-amber-600">
                        {{ summary.totalVerified }}
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
                                New Tutors
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                New Guardians
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Total
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Active Tutors
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Active Guardians
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Verified Tutors
                            </th>
                            <th
                                class="px-5 py-4 text-right text-xs font-semibold tracking-wider text-slate-500 uppercase"
                            >
                                Verified Guardians
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
                            <td
                                class="px-5 py-4 text-right font-medium text-blue-600"
                            >
                                {{ row.newTutors }}
                            </td>
                            <td
                                class="px-5 py-4 text-right font-medium text-purple-600"
                            >
                                {{ row.newGuardians }}
                            </td>
                            <td class="px-5 py-4 text-right font-semibold">
                                {{ row.totalRegistrations }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-600">
                                {{ row.activeTutors }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-600">
                                {{ row.activeGuardians }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-600">
                                {{ row.verifiedTutors }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-600">
                                {{ row.verifiedGuardians }}
                            </td>
                        </tr>
                        <tr v-if="reportData.length === 0" class="border-t">
                            <td
                                colspan="8"
                                class="px-5 py-8 text-center text-muted-foreground"
                            >
                                No registration data found for the selected
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
                                {{ summary.totalTutors }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{ summary.totalGuardians }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{ summary.totalRegistrations }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{ summary.totalActive }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                —
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                {{ summary.totalVerified }}
                            </td>
                            <td class="px-5 py-4 text-right text-slate-900">
                                —
                            </td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
