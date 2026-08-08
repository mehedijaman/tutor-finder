<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Bell,
    Briefcase,
    Check,
    Clock,
    FileText,
    PlusCircle,
    ShieldAlert,
    User,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed } from 'vue';
import ActionCard from '@/components/ActionCard.vue';
import DashboardWelcome from '@/components/DashboardWelcome.vue';
import NoticeCarousel from '@/components/NoticeCarousel.vue';
import StatCard from '@/components/StatCard.vue';
import GuardianLayout from '@/layouts/GuardianLayout.vue';
import type { ActionCardConfig, StatCardConfig } from '@/types/dashboard';

interface Notice {
    id: number;
    title: string;
    body: string;
    published_at: string;
    expires_at: string | null;
}

interface VerificationFeeInvoice {
    id: number;
    invoice_no: string;
    status: { value: string } | string;
    amount: number;
    currency: string;
    due_at: string | null;
}

const props = defineProps<{
    notices: Notice[];
    jobStats: {
        pending: number;
        live: number;
        confirmed: number;
        cancelled: number;
        closed: number;
    };
    verificationStatus: string;
    isVerified: boolean;
    verificationFeeInvoice: VerificationFeeInvoice | null;
    pendingInvoiceCount: number;
}>();

const breadcrumbs = [{ title: 'Dashboard', href: '/guardian/dashboard' }];

const statCards = computed<StatCardConfig[]>(() => [
    {
        label: 'Pending',
        value: props.jobStats.pending,
        href: '/guardian/jobs/pending',
        icon: FileText,
        color: 'amber',
        subValue: 'Awaiting review',
    },
    {
        label: 'Live',
        value: props.jobStats.live,
        href: '/guardian/jobs/live',
        icon: Briefcase,
        color: 'blue',
        subValue: 'Open for applications',
    },
    {
        label: 'Confirmed',
        value: props.jobStats.confirmed,
        href: '/guardian/jobs/confirmed',
        icon: Check,
        color: 'emerald',
        subValue: 'Successful hires',
    },
    {
        label: 'Cancelled',
        value: props.jobStats.cancelled,
        href: '/guardian/jobs/cancelled',
        icon: X,
        color: 'rose',
        subValue: 'Closed by guardian',
    },
    {
        label: 'Closed',
        value: props.jobStats.closed,
        href: '/guardian/jobs/closed',
        icon: Briefcase,
        color: 'slate',
        subValue: 'Archived jobs',
    },
]);

const quickActions: ActionCardConfig[] = [
    {
        title: 'Post New Job',
        description: 'Create a new tutoring requirement in minutes.',
        href: '/guardian/jobs/create',
        icon: PlusCircle,
        color: 'blue',
    },
    {
        title: 'Hiring Pipeline',
        description: 'Review live, confirmed, and closed job activity.',
        href: '/guardian/jobs',
        icon: Briefcase,
        color: 'emerald',
    },
    {
        title: 'My Profile',
        description: 'Update your contact info and preferences.',
        href: '/guardian/profile',
        icon: User,
        color: 'violet',
    },
    {
        title: 'Payments & Escrow',
        description: 'View invoices and manage secure payments.',
        href: '/guardian/finance/invoices',
        icon: Wallet,
        color: 'amber',
    },
    {
        title: 'Verification',
        description: 'Verify your account for enhanced trust.',
        href: '/guardian/verification',
        icon: FileText,
        color: 'cyan',
    },
    {
        title: 'Notifications',
        description: 'Monitor application updates and hiring milestones.',
        href: '/guardian/notifications',
        icon: Bell,
        color: 'rose',
    },
];

const verificationLabel = computed(() => {
    const map: Record<string, string> = {
        unverified: 'Not Verified',
        pending: 'Pending Review',
        approved: 'Approved',
        invoiced: 'Invoice Issued',
        verified: 'Verified',
        rejected: 'Rejected',
        cancelled: 'Cancelled',
    };
    return map[props.verificationStatus] ?? props.verificationStatus;
});

const verificationFeeInvoiceStatus = computed<string>(() => {
    const inv = props.verificationFeeInvoice;
    if (!inv) {
        return '';
    }
    const s =
        typeof inv.status === 'object' ? inv.status.value : (inv.status ?? '');
    return s;
});
</script>

<template>
    <Head title="Guardian Panel" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Welcome Banner with Verified Badge -->
            <div class="relative">
                <DashboardWelcome />
                <!-- Verified badge overlay -->
                <div
                    v-if="isVerified"
                    class="absolute top-3 right-3 flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300"
                >
                    <BadgeCheck class="h-3.5 w-3.5" />
                    Verified Profile
                </div>
            </div>

            <NoticeCarousel v-if="notices.length > 0" :notices="notices" />

            <!-- Job Overview Stats -->
            <div>
                <h2
                    class="mb-3 text-sm font-bold tracking-wide text-slate-500 uppercase dark:text-slate-400"
                >
                    Job Overview
                </h2>
                <div
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5"
                >
                    <StatCard
                        v-for="card in statCards"
                        :key="card.label"
                        v-bind="card"
                    />
                </div>
            </div>

            <!-- Account Status Cards -->
            <div>
                <h2
                    class="mb-3 text-sm font-bold tracking-wide text-slate-500 uppercase dark:text-slate-400"
                >
                    Account Status
                </h2>
                <div
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <!-- Verification Status Card -->
                    <Link
                        href="/guardian/profile"
                        class="group flex items-center gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm transition hover:shadow-md"
                        :class="{
                            'border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/60 dark:bg-emerald-950/30':
                                isVerified,
                            'border-amber-200 bg-amber-50/30 dark:border-amber-900/60 dark:bg-amber-950/30':
                                verificationStatus === 'pending',
                            'border-slate-200 dark:border-slate-800':
                                !isVerified && verificationStatus !== 'pending',
                        }"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="{
                                'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300':
                                    isVerified,
                                'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300':
                                    verificationStatus === 'pending',
                                'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400':
                                    !isVerified &&
                                    verificationStatus !== 'pending',
                            }"
                        >
                            <BadgeCheck v-if="isVerified" class="h-5 w-5" />
                            <Clock
                                v-else-if="verificationStatus === 'pending'"
                                class="h-5 w-5"
                            />
                            <ShieldAlert v-else class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-xs font-semibold text-slate-400 uppercase dark:text-slate-500"
                            >
                                Profile Verification
                            </p>
                            <p
                                class="mt-0.5 text-sm font-bold"
                                :class="{
                                    'text-emerald-700 dark:text-emerald-300':
                                        isVerified,
                                    'text-amber-700 dark:text-amber-300':
                                        verificationStatus === 'pending',
                                    'text-slate-700 dark:text-slate-300':
                                        !isVerified &&
                                        verificationStatus !== 'pending',
                                }"
                            >
                                {{ verificationLabel }}
                            </p>
                        </div>
                    </Link>

                    <!-- Verification Fee Card (shown only when invoice exists) -->
                    <Link
                        v-if="verificationFeeInvoice"
                        href="/guardian/finance/invoices"
                        class="group flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50/40 p-4 shadow-sm transition hover:shadow-md dark:border-amber-900/60 dark:bg-amber-950/30"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300"
                        >
                            <FileText class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-xs font-semibold text-amber-500 uppercase dark:text-amber-400"
                            >
                                Verification Fee
                            </p>
                            <p
                                class="mt-0.5 text-sm font-bold text-amber-700 dark:text-amber-300"
                            >
                                {{ verificationFeeInvoice.currency }}
                                {{ verificationFeeInvoice.amount }}
                                <span
                                    class="ml-1 text-xs font-semibold capitalize"
                                >
                                    — {{ verificationFeeInvoiceStatus }}
                                </span>
                            </p>
                        </div>
                    </Link>

                    <!-- Payments & Escrow Card -->
                    <Link
                        href="/guardian/finance/invoices"
                        class="group flex items-center gap-4 rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
                        :class="
                            pendingInvoiceCount > 0
                                ? 'border-rose-200 bg-rose-50/40 dark:border-rose-900/60 dark:bg-rose-950/30'
                                : 'border-border bg-card'
                        "
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="
                                pendingInvoiceCount > 0
                                    ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-300'
                                    : 'bg-blue-50 text-blue-500 dark:bg-slate-800 dark:text-blue-400'
                            "
                        >
                            <Wallet class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="text-xs font-semibold text-slate-400 uppercase dark:text-slate-500"
                            >
                                Payments & Escrow
                            </p>
                            <p
                                class="mt-0.5 text-sm font-bold"
                                :class="
                                    pendingInvoiceCount > 0
                                        ? 'text-rose-700 dark:text-rose-300'
                                        : 'text-slate-600 dark:text-slate-300'
                                "
                            >
                                <span v-if="pendingInvoiceCount > 0">
                                    {{ pendingInvoiceCount }} pending invoice{{
                                        pendingInvoiceCount > 1 ? 's' : ''
                                    }}
                                </span>
                                <span v-else>No pending invoices</span>
                            </p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Quick Actions -->
            <div>
                <h2
                    class="mb-3 text-sm font-bold tracking-wide text-slate-500 uppercase dark:text-slate-400"
                >
                    Quick Actions
                </h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <ActionCard
                        v-for="action in quickActions"
                        :key="action.title"
                        v-bind="action"
                    />
                </div>
            </div>
        </div>
    </GuardianLayout>
</template>
