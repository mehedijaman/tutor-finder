<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import {
    ArrowRight,
    ArrowUpRight,
    Banknote,
    Briefcase,
    CheckCircle,
    Clock,
    CreditCard,
    FileCheck,
    GraduationCap,
    Layout,
    MessageSquare,
    Settings,
    Shield,
    TicketCheck,
    TrendingUp,
    UserCheck,
    Users,
    XCircle,
} from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed } from 'vue';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import AdminLayout from '@/layouts/AdminLayout.vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Tooltip, Legend, Filler);

interface UserStats {
    totalTutors: number;
    activeTutors: number;
    totalGuardians: number;
    activeGuardians: number;
}

interface JobStats {
    pending: number;
    live: number;
    confirmed: number;
    cancelled: number;
    closed: number;
    total: number;
}

interface ApplicationStats {
    applied: number;
    shortlisted: number;
    appointed: number;
    confirmed: number;
    cancelled: number;
}

interface TicketStats {
    open: number;
    inProgress: number;
    closed: number;
}

interface VerificationStats {
    pending: number;
    approved: number;
    verified: number;
    rejected: number;
}

interface FinanceStats {
    totalRevenue: number;
    monthlyRevenue: number;
    unpaidInvoices: number;
    pendingRefunds: number;
}

interface ChartData {
    labels: string[];
    newTutors: number[];
    newGuardians: number[];
    newJobs: number[];
    revenue: number[];
}

interface RecentJob {
    id: number;
    title: string;
    status: string;
    statusLabel: string;
    guardian: string;
    createdAt: string;
}

interface RecentTicket {
    id: number;
    ticketNumber: string;
    subject: string;
    status: string;
    statusLabel: string;
    priority: string;
    priorityLabel: string;
    user: string;
    createdAt: string;
}

interface PendingVerification {
    id: number;
    userName: string;
    userRole: string;
    roleLabel: string;
    createdAt: string;
}

const props = defineProps<{
    stats: {
        users: UserStats;
        jobs: JobStats;
        applications: ApplicationStats;
        tickets: TicketStats;
        verifications: VerificationStats;
        finance: FinanceStats;
        contactMessages: { open: number };
    };
    charts: ChartData;
    recentActivity: {
        recentJobs: RecentJob[];
        recentTickets: RecentTicket[];
        pendingVerifications: PendingVerification[];
    };
}>();

const breadcrumbs = [{ title: 'Admin Dashboard', href: '/admin/dashboard' }];

function formatNumber(value: number): string {
    if (value >= 1000000) {
        return (value / 1000000).toFixed(1) + 'M';
    }
    if (value >= 1000) {
        return (value / 1000).toFixed(1) + 'K';
    }
    return value.toLocaleString();
}

function formatCurrency(value: number): string {
    return '৳' + formatNumber(value);
}

interface OverviewCard {
    label: string;
    value: string;
    subValue: string;
    href: string;
    icon: Component;
    iconBg: string;
    iconColor: string;
    borderHover: string;
    gradientBar: string;
}

const overviewCards = computed<OverviewCard[]>(() => [
    {
        label: 'Total Tutors',
        value: formatNumber(props.stats.users.totalTutors),
        subValue: `${formatNumber(props.stats.users.activeTutors)} active`,
        href: '/admin/tutors',
        icon: GraduationCap,
        iconBg: 'bg-blue-50 dark:bg-blue-950/40',
        iconColor: 'text-blue-600 dark:text-blue-400',
        borderHover: 'hover:border-blue-200 dark:hover:border-blue-800',
        gradientBar: 'from-blue-500 to-indigo-500',
    },
    {
        label: 'Total Guardians',
        value: formatNumber(props.stats.users.totalGuardians),
        subValue: `${formatNumber(props.stats.users.activeGuardians)} active`,
        href: '/admin/guardians',
        icon: UserCheck,
        iconBg: 'bg-emerald-50 dark:bg-emerald-950/40',
        iconColor: 'text-emerald-600 dark:text-emerald-400',
        borderHover: 'hover:border-emerald-200 dark:hover:border-emerald-800',
        gradientBar: 'from-emerald-500 to-teal-500',
    },
    {
        label: 'Total Jobs',
        value: formatNumber(props.stats.jobs.total),
        subValue: `${formatNumber(props.stats.jobs.live)} live now`,
        href: '/admin/jobs',
        icon: Briefcase,
        iconBg: 'bg-violet-50 dark:bg-violet-950/40',
        iconColor: 'text-violet-600 dark:text-violet-400',
        borderHover: 'hover:border-violet-200 dark:hover:border-violet-800',
        gradientBar: 'from-violet-500 to-purple-500',
    },
    {
        label: 'Monthly Revenue',
        value: formatCurrency(props.stats.finance.monthlyRevenue),
        subValue: `${formatCurrency(props.stats.finance.totalRevenue)} total`,
        href: '/admin/finance/invoices',
        icon: TrendingUp,
        iconBg: 'bg-amber-50 dark:bg-amber-950/40',
        iconColor: 'text-amber-600 dark:text-amber-400',
        borderHover: 'hover:border-amber-200 dark:hover:border-amber-800',
        gradientBar: 'from-amber-500 to-orange-500',
    },
]);

interface AttentionItem {
    label: string;
    count: number;
    href: string;
    color: string;
    bgColor: string;
    icon: Component;
}

const attentionItems = computed<AttentionItem[]>(() => {
    const items: AttentionItem[] = [];
    if (props.stats.jobs.pending > 0) {
        items.push({
            label: 'Pending Jobs',
            count: props.stats.jobs.pending,
            href: '/admin/jobs/pending',
            color: 'text-amber-700 dark:text-amber-400',
            bgColor: 'bg-amber-50 dark:bg-amber-950/40',
            icon: Clock,
        });
    }
    if (props.stats.tickets.open > 0) {
        items.push({
            label: 'Open Tickets',
            count: props.stats.tickets.open,
            href: '/admin/tickets',
            color: 'text-rose-700 dark:text-rose-400',
            bgColor: 'bg-rose-50 dark:bg-rose-950/40',
            icon: TicketCheck,
        });
    }
    if (props.stats.verifications.pending > 0) {
        items.push({
            label: 'Pending Verifications',
            count: props.stats.verifications.pending,
            href: '/admin/verifications/pending',
            color: 'text-blue-700 dark:text-blue-400',
            bgColor: 'bg-blue-50 dark:bg-blue-950/40',
            icon: FileCheck,
        });
    }
    if (props.stats.finance.unpaidInvoices > 0) {
        items.push({
            label: 'Unpaid Invoices',
            count: props.stats.finance.unpaidInvoices,
            href: '/admin/finance/invoices',
            color: 'text-orange-700 dark:text-orange-400',
            bgColor: 'bg-orange-50 dark:bg-orange-950/40',
            icon: CreditCard,
        });
    }
    if (props.stats.finance.pendingRefunds > 0) {
        items.push({
            label: 'Pending Refunds',
            count: props.stats.finance.pendingRefunds,
            href: '/admin/finance/refund-requests',
            color: 'text-purple-700 dark:text-purple-400',
            bgColor: 'bg-purple-50 dark:bg-purple-950/40',
            icon: Banknote,
        });
    }
    if (props.stats.contactMessages.open > 0) {
        items.push({
            label: 'Open Messages',
            count: props.stats.contactMessages.open,
            href: '/admin/contact-messages',
            color: 'text-cyan-700 dark:text-cyan-400',
            bgColor: 'bg-cyan-50 dark:bg-cyan-950/40',
            icon: MessageSquare,
        });
    }
    return items;
});

const userRegistrationChartData = computed(() => ({
    labels: props.charts.labels.map((l: string) => l.split(' ')[0]),
    datasets: [
        {
            label: 'Tutors',
            data: props.charts.newTutors,
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            hoverBackgroundColor: 'rgba(59, 130, 246, 1)',
            borderRadius: 4,
            borderSkipped: false,
        },
        {
            label: 'Guardians',
            data: props.charts.newGuardians,
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            hoverBackgroundColor: 'rgba(16, 185, 129, 1)',
            borderRadius: 4,
            borderSkipped: false,
        },
    ],
}));

const userRegistrationChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleColor: '#fff',
            bodyColor: '#cbd5e1',
            cornerRadius: 8,
            padding: 10,
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: '#94a3b8', font: { size: 10 } },
            border: { display: false },
        },
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(148, 163, 184, 0.1)' },
            ticks: {
                color: '#94a3b8',
                font: { size: 10 },
                precision: 0,
            },
            border: { display: false },
        },
    },
}));

const revenueChartData = computed(() => ({
    labels: props.charts.labels.map((l: string) => l.split(' ')[0]),
    datasets: [
        {
            label: 'Revenue',
            data: props.charts.revenue,
            borderColor: 'rgba(245, 158, 11, 1)',
            backgroundColor: 'rgba(245, 158, 11, 0.15)',
            pointBackgroundColor: 'rgba(245, 158, 11, 1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.4,
            borderWidth: 2.5,
        },
    ],
}));

const revenueChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleColor: '#fff',
            bodyColor: '#cbd5e1',
            cornerRadius: 8,
            padding: 10,
            callbacks: {
                label: (context: { parsed: { y: number } }) => `৳${context.parsed.y.toLocaleString()}`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: '#94a3b8', font: { size: 10 } },
            border: { display: false },
        },
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(148, 163, 184, 0.1)' },
            ticks: {
                color: '#94a3b8',
                font: { size: 10 },
                callback: (value: number) => (value >= 1000 ? `৳${value / 1000}K` : `৳${value}`),
            },
            border: { display: false },
        },
    },
}));

const jobDoughnutData = computed(() => ({
    labels: ['Pending', 'Live', 'Confirmed', 'Cancelled', 'Closed'],
    datasets: [
        {
            data: [
                props.stats.jobs.pending,
                props.stats.jobs.live,
                props.stats.jobs.confirmed,
                props.stats.jobs.cancelled,
                props.stats.jobs.closed,
            ],
            backgroundColor: [
                'rgba(245, 158, 11, 0.85)',
                'rgba(59, 130, 246, 0.85)',
                'rgba(16, 185, 129, 0.85)',
                'rgba(244, 63, 94, 0.85)',
                'rgba(148, 163, 184, 0.7)',
            ],
            hoverBackgroundColor: [
                'rgba(245, 158, 11, 1)',
                'rgba(59, 130, 246, 1)',
                'rgba(16, 185, 129, 1)',
                'rgba(244, 63, 94, 1)',
                'rgba(148, 163, 184, 1)',
            ],
            borderWidth: 0,
            cutout: '72%',
            borderRadius: 3,
        },
    ],
}));

const jobDoughnutOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleColor: '#fff',
            bodyColor: '#cbd5e1',
            cornerRadius: 8,
            padding: 10,
        },
    },
}));

const jobStatusBreakdown = computed(() => [
    { label: 'Pending', value: props.stats.jobs.pending, color: 'bg-amber-500' },
    { label: 'Live', value: props.stats.jobs.live, color: 'bg-blue-500' },
    { label: 'Confirmed', value: props.stats.jobs.confirmed, color: 'bg-emerald-500' },
    { label: 'Cancelled', value: props.stats.jobs.cancelled, color: 'bg-rose-500' },
    { label: 'Closed', value: props.stats.jobs.closed, color: 'bg-slate-400' },
]);

function statusBadgeClass(status: string): string {
    const map: Record<string, string> = {
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
        live: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
        confirmed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
        cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400',
        closed: 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-400',
        open: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
        in_progress: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
    };
    return map[status] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-400';
}

function priorityBadgeClass(priority: string): string {
    const map: Record<string, string> = {
        low: 'bg-slate-100 text-slate-600 dark:bg-slate-700/40 dark:text-slate-400',
        medium: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
        high: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
        urgent: 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400',
    };
    return map[priority] ?? 'bg-slate-100 text-slate-600';
}

const quickActions = [
    {
        title: 'Manage Users',
        description: 'Admin accounts & roles',
        href: '/admin/users',
        icon: Users,
        gradient: 'from-blue-500 to-blue-600',
        shadow: 'shadow-blue-500/30',
        hoverBorder: 'hover:border-blue-200',
        arrowColor: 'group-hover:text-blue-600',
    },
    {
        title: 'Roles & Permissions',
        description: 'Access control management',
        href: '/admin/roles',
        icon: Shield,
        gradient: 'from-violet-500 to-violet-600',
        shadow: 'shadow-violet-500/30',
        hoverBorder: 'hover:border-violet-200',
        arrowColor: 'group-hover:text-violet-600',
    },
    {
        title: 'Manage Tutors',
        description: 'Profiles & verification',
        href: '/admin/tutors',
        icon: GraduationCap,
        gradient: 'from-emerald-500 to-emerald-600',
        shadow: 'shadow-emerald-500/30',
        hoverBorder: 'hover:border-emerald-200',
        arrowColor: 'group-hover:text-emerald-600',
    },
    {
        title: 'Manage Guardians',
        description: 'Profiles & activity',
        href: '/admin/guardians',
        icon: UserCheck,
        gradient: 'from-amber-500 to-amber-600',
        shadow: 'shadow-amber-500/30',
        hoverBorder: 'hover:border-amber-200',
        arrowColor: 'group-hover:text-amber-600',
    },
    {
        title: 'Verifications',
        description: 'Approve & review',
        href: '/admin/verifications',
        icon: FileCheck,
        gradient: 'from-cyan-500 to-cyan-600',
        shadow: 'shadow-cyan-500/30',
        hoverBorder: 'hover:border-cyan-200',
        arrowColor: 'group-hover:text-cyan-600',
    },
    {
        title: 'Finance',
        description: 'Invoices & payments',
        href: '/admin/finance/invoices',
        icon: CreditCard,
        gradient: 'from-rose-500 to-rose-600',
        shadow: 'shadow-rose-500/30',
        hoverBorder: 'hover:border-rose-200',
        arrowColor: 'group-hover:text-rose-600',
    },
    {
        title: 'CMS & Content',
        description: 'Pages, blog & FAQs',
        href: '/admin/blog/posts',
        icon: Layout,
        gradient: 'from-indigo-500 to-indigo-600',
        shadow: 'shadow-indigo-500/30',
        hoverBorder: 'hover:border-indigo-200',
        arrowColor: 'group-hover:text-indigo-600',
    },
    {
        title: 'Settings',
        description: 'Site & system config',
        href: '/settings/site',
        icon: Settings,
        gradient: 'from-slate-600 to-slate-700',
        shadow: 'shadow-slate-500/30',
        hoverBorder: 'hover:border-slate-300',
        arrowColor: 'group-hover:text-slate-600',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4 sm:p-6 lg:p-8">
            <!-- Overview Stat Cards -->
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <Link
                    v-for="card in overviewCards"
                    :key="card.label"
                    :href="card.href"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-700/60 dark:bg-slate-900"
                    :class="card.borderHover"
                >
                    <div
                        class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r"
                        :class="card.gradientBar"
                    ></div>
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] font-semibold tracking-[0.15em] text-slate-400 uppercase dark:text-slate-500">
                                {{ card.label }}
                            </p>
                            <p class="mt-3 text-[2rem] leading-none font-extrabold tracking-tight text-slate-900 dark:text-white">
                                {{ card.value }}
                            </p>
                            <p class="mt-2 text-sm font-medium text-slate-500 dark:text-slate-400">
                                {{ card.subValue }}
                            </p>
                        </div>
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl ring-1 ring-slate-900/5 dark:ring-slate-700/50"
                            :class="card.iconBg"
                        >
                            <component :is="card.icon" class="h-7 w-7" :class="card.iconColor" />
                        </div>
                    </div>
                    <ArrowUpRight
                        class="absolute top-5 right-5 h-4 w-4 text-slate-300 opacity-0 transition-all duration-300 group-hover:opacity-100 dark:text-slate-600"
                    />
                </Link>
            </div>

            <!-- Needs Attention Banner -->
            <div
                v-if="attentionItems.length > 0"
                class="rounded-2xl border border-amber-200/60 bg-gradient-to-r from-amber-50/80 to-orange-50/40 p-4 sm:p-5 dark:border-amber-900/40 dark:from-amber-950/20 dark:to-orange-950/10"
            >
                <h3 class="mb-3 text-sm font-semibold tracking-wide text-amber-800 uppercase dark:text-amber-400">
                    Needs Attention
                </h3>
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <Link
                        v-for="item in attentionItems"
                        :key="item.label"
                        :href="item.href"
                        class="group flex items-center gap-2 rounded-xl border border-white/60 px-3 py-2 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md sm:px-4 sm:py-2.5"
                        :class="item.bgColor"
                    >
                        <component :is="item.icon" class="h-4 w-4 shrink-0" :class="item.color" />
                        <span class="text-sm font-semibold" :class="item.color">
                            {{ item.count }}
                        </span>
                        <span class="hidden text-sm font-medium text-slate-700 sm:inline dark:text-slate-300">
                            {{ item.label }}
                        </span>
                        <ArrowRight
                            class="h-3.5 w-3.5 text-slate-400 transition-transform group-hover:translate-x-0.5"
                        />
                    </Link>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- User Registration Chart -->
                <div class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-700/60 dark:bg-slate-900">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                                User Registrations
                            </h3>
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                                Last 12 months
                            </p>
                        </div>
                        <div class="flex items-center gap-4 text-xs">
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                                Tutors
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                Guardians
                            </span>
                        </div>
                    </div>
                    <div class="relative h-56 sm:h-64">
                        <Bar :data="userRegistrationChartData" :options="userRegistrationChartOptions" />
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-700/60 dark:bg-slate-900">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                                Revenue Trend
                            </h3>
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                                Last 12 months
                            </p>
                        </div>
                        <Link
                            href="/admin/finance/invoices"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                        >
                            View all
                        </Link>
                    </div>
                    <div class="relative h-56 sm:h-64">
                        <Line :data="revenueChartData" :options="revenueChartOptions" />
                    </div>
                </div>
            </div>

            <!-- Jobs Breakdown + Platform Health -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Job Status Breakdown -->
                <div class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-700/60 dark:bg-slate-900">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                            Job Status Breakdown
                        </h3>
                        <Link
                            href="/admin/jobs"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                        >
                            View all
                        </Link>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="relative h-40 w-40 shrink-0 sm:h-44 sm:w-44">
                            <Doughnut :data="jobDoughnutData" :options="jobDoughnutOptions" />
                            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-extrabold tabular-nums text-slate-900 dark:text-white">
                                    {{ stats.jobs.total }}
                                </span>
                                <span class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase dark:text-slate-500">
                                    Total
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col gap-2">
                            <div
                                v-for="segment in jobStatusBreakdown"
                                :key="segment.label"
                                class="flex items-center justify-between rounded-lg px-3 py-2 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50"
                            >
                                <div class="flex items-center gap-2.5">
                                    <span class="inline-block h-2.5 w-2.5 rounded-full" :class="segment.color"></span>
                                    <span class="text-sm font-medium text-slate-600 dark:text-slate-300">
                                        {{ segment.label }}
                                    </span>
                                </div>
                                <span class="text-sm font-bold tabular-nums text-slate-900 dark:text-white">
                                    {{ segment.value }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket & Verification Summary -->
                <div class="rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-700/60 dark:bg-slate-900">
                    <h3 class="mb-5 text-base font-semibold text-slate-900 dark:text-white">
                        Platform Health
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-4 dark:border-blue-900/30 dark:bg-blue-950/20">
                            <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                                <TicketCheck class="h-4 w-4" />
                                <span class="text-[10px] font-semibold tracking-wider uppercase">Open Tickets</span>
                            </div>
                            <p class="mt-2.5 text-2xl font-extrabold tabular-nums text-blue-700 dark:text-blue-300">
                                {{ stats.tickets.open }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-4 dark:border-amber-900/30 dark:bg-amber-950/20">
                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                                <Clock class="h-4 w-4" />
                                <span class="text-[10px] font-semibold tracking-wider uppercase">In Progress</span>
                            </div>
                            <p class="mt-2.5 text-2xl font-extrabold tabular-nums text-amber-700 dark:text-amber-300">
                                {{ stats.tickets.inProgress }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4 dark:border-emerald-900/30 dark:bg-emerald-950/20">
                            <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                                <CheckCircle class="h-4 w-4" />
                                <span class="text-[10px] font-semibold tracking-wider uppercase">Verified Users</span>
                            </div>
                            <p class="mt-2.5 text-2xl font-extrabold tabular-nums text-emerald-700 dark:text-emerald-300">
                                {{ stats.verifications.verified }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-rose-100 bg-rose-50/60 p-4 dark:border-rose-900/30 dark:bg-rose-950/20">
                            <div class="flex items-center gap-2 text-rose-600 dark:text-rose-400">
                                <XCircle class="h-4 w-4" />
                                <span class="text-[10px] font-semibold tracking-wider uppercase">Rejected</span>
                            </div>
                            <p class="mt-2.5 text-2xl font-extrabold tabular-nums text-rose-700 dark:text-rose-300">
                                {{ stats.verifications.rejected }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Tables -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Recent Jobs -->
                <div class="rounded-2xl border border-slate-200/60 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                            Recent Jobs
                        </h3>
                        <Link
                            href="/admin/jobs"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                        >
                            View all
                        </Link>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div
                            v-for="job in recentActivity.recentJobs"
                            :key="job.id"
                            class="group flex items-center gap-3 px-5 py-3.5 transition-colors hover:bg-slate-50 sm:px-6 dark:hover:bg-slate-800/50"
                        >
                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="`/admin/jobs/${job.id}/edit`"
                                    class="truncate text-sm font-medium text-slate-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                                >
                                    {{ job.title }}
                                </Link>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ job.guardian }} · {{ job.createdAt }}
                                </p>
                            </div>
                            <span
                                class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="statusBadgeClass(job.status)"
                            >
                                {{ job.statusLabel }}
                            </span>
                        </div>
                        <div
                            v-if="recentActivity.recentJobs.length === 0"
                            class="px-5 py-8 text-center text-sm text-slate-500 sm:px-6 dark:text-slate-400"
                        >
                            No recent jobs found.
                        </div>
                    </div>
                </div>

                <!-- Recent Tickets -->
                <div class="rounded-2xl border border-slate-200/60 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                            Recent Tickets
                        </h3>
                        <Link
                            href="/admin/tickets"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                        >
                            View all
                        </Link>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div
                            v-for="ticket in recentActivity.recentTickets"
                            :key="ticket.id"
                            class="group flex items-center gap-3 px-5 py-3.5 transition-colors hover:bg-slate-50 sm:px-6 dark:hover:bg-slate-800/50"
                        >
                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="`/admin/tickets/${ticket.id}`"
                                    class="truncate text-sm font-medium text-slate-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                                >
                                    {{ ticket.ticketNumber }}
                                    <span class="font-normal text-slate-500 dark:text-slate-400">
                                        — {{ ticket.subject }}
                                    </span>
                                </Link>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ ticket.user }} · {{ ticket.createdAt }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1.5">
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="priorityBadgeClass(ticket.priority)"
                                >
                                    {{ ticket.priorityLabel }}
                                </span>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="statusBadgeClass(ticket.status)"
                                >
                                    {{ ticket.statusLabel }}
                                </span>
                            </div>
                        </div>
                        <div
                            v-if="recentActivity.recentTickets.length === 0"
                            class="px-5 py-8 text-center text-sm text-slate-500 sm:px-6 dark:text-slate-400"
                        >
                            No recent tickets found.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Verifications -->
            <div
                v-if="recentActivity.pendingVerifications.length > 0"
                class="rounded-2xl border border-slate-200/60 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                        Pending Verifications
                    </h3>
                    <Link
                        href="/admin/verifications/pending"
                        class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >
                        View all
                    </Link>
                </div>
                <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
                    <Link
                        v-for="v in recentActivity.pendingVerifications"
                        :key="v.id"
                        :href="`/admin/verifications/${v.id}`"
                        class="group flex items-center gap-3 rounded-xl border border-slate-200/80 p-3.5 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md dark:border-slate-700 dark:hover:border-blue-800"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-950/40">
                            <FileCheck class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900 dark:text-white">
                                {{ v.userName }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ v.roleLabel }} · {{ v.createdAt }}
                            </p>
                        </div>
                        <ArrowRight
                            class="h-4 w-4 shrink-0 text-slate-300 transition-transform group-hover:translate-x-0.5 group-hover:text-blue-500 dark:text-slate-600"
                        />
                    </Link>
                </div>
            </div>

            <!-- Quick Access -->
            <div>
                <h3 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">
                    Quick Access
                </h3>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Link
                        v-for="action in quickActions"
                        :key="action.title"
                        :href="action.href"
                        class="group relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-700/60 dark:bg-slate-900"
                        :class="action.hoverBorder"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br text-white shadow-lg transition-transform duration-300 group-hover:scale-110"
                            :class="[action.gradient, action.shadow]"
                        >
                            <component :is="action.icon" class="h-5 w-5" />
                        </div>
                        <h4 class="mt-3.5 text-sm font-semibold tracking-tight text-slate-900 dark:text-white">
                            {{ action.title }}
                        </h4>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            {{ action.description }}
                        </p>
                        <ArrowRight
                            class="mt-3 h-4 w-4 text-slate-400 transition-transform duration-300 group-hover:translate-x-1"
                            :class="action.arrowColor"
                        />
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
