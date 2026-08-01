<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Bell,
    Briefcase,
    ClipboardCheck,
    Clock,
    FileText,
    Search,
    ShieldAlert,
    User,
    UserCheck,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed } from 'vue';
import ActionCard from '@/components/ActionCard.vue';
import DashboardWelcome from '@/components/DashboardWelcome.vue';
import NoticeCarousel from '@/components/NoticeCarousel.vue';
import StatCard from '@/components/StatCard.vue';
import TutorLayout from '@/layouts/TutorLayout.vue';
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
    applicationStats: {
        applied: number;
        shortlisted: number;
        appointed: number;
        confirmed: number;
        cancelled: number;
    };
    verificationStatus: string;
    isVerified: boolean;
    verificationFeeInvoice: VerificationFeeInvoice | null;
    pendingInvoiceCount: number;
}>();

const breadcrumbs = [{ title: 'Dashboard', href: '/tutor/dashboard' }];

const statCards = computed<StatCardConfig[]>(() => [
    {
        label: 'Applied',
        value: props.applicationStats.applied,
        href: '/tutor/job-applications',
        icon: Briefcase,
        color: 'slate',
        subValue: 'Open applications',
    },
    {
        label: 'Shortlisted',
        value: props.applicationStats.shortlisted,
        href: '/tutor/job-applications/shortlisted',
        icon: ClipboardCheck,
        color: 'blue',
        subValue: 'Review prospects',
    },
    {
        label: 'Appointed',
        value: props.applicationStats.appointed,
        href: '/tutor/job-applications/appointed',
        icon: UserCheck,
        color: 'amber',
        subValue: 'Pending final steps',
    },
    {
        label: 'Confirmed',
        value: props.applicationStats.confirmed,
        href: '/tutor/job-applications/confirmed',
        icon: BadgeCheck,
        color: 'emerald',
        subValue: 'Successful matches',
    },
    {
        label: 'Cancelled',
        value: props.applicationStats.cancelled,
        href: '/tutor/job-applications/cancelled',
        icon: X,
        color: 'rose',
        subValue: 'Cancelled requests',
    },
]);

const quickActions: ActionCardConfig[] = [
    {
        title: 'Browse Jobs',
        description: 'Discover new tutoring opportunities matching your skills.',
        href: '/jobs',
        icon: Search,
        color: 'blue',
    },
    {
        title: 'Application Pipeline',
        description: 'Track applied, shortlisted, and appointed positions.',
        href: '/tutor/job-applications',
        icon: Briefcase,
        color: 'emerald',
    },
    {
        title: 'My Profile',
        description: 'Update your bio, education, and teaching preferences.',
        href: '/tutor/profile',
        icon: User,
        color: 'violet',
    },
    {
        title: 'Fees & Invoices',
        description: 'View invoices, payment history, and refund requests.',
        href: '/tutor/finance/invoices',
        icon: Wallet,
        color: 'amber',
    },
    {
        title: 'Verification',
        description: 'Get verified to build trust with guardians.',
        href: '/tutor/verification',
        icon: FileText,
        color: 'cyan',
    },
    {
        title: 'Notifications',
        description: 'Stay updated on opportunities and status changes.',
        href: '/tutor/notifications',
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
    const s = typeof inv.status === 'object' ? inv.status.value : (inv.status ?? '');
    return s;
});
</script>

<template>
    <Head title="Tutor Panel" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- Welcome Banner with Verified Badge -->
            <div class="relative">
                <DashboardWelcome />
                <div
                    v-if="isVerified"
                    class="absolute top-3 right-3 flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 shadow-sm"
                >
                    <BadgeCheck class="h-3.5 w-3.5" />
                    Verified Tutor
                </div>
            </div>

            <NoticeCarousel
                v-if="notices.length > 0"
                :notices="notices"
            />

            <!-- Application Overview Stats -->
            <div>
                <h2 class="mb-3 text-sm font-bold tracking-wide text-slate-500 uppercase">
                    Application Overview
                </h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
                    <StatCard
                        v-for="card in statCards"
                        :key="card.label"
                        v-bind="card"
                    />
                </div>
            </div>

            <!-- Account Status Cards -->
            <div>
                <h2 class="mb-3 text-sm font-bold tracking-wide text-slate-500 uppercase">
                    Account Status
                </h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Verification Status Card -->
                    <Link
                        href="/tutor/profile"
                        class="group flex items-center gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm transition hover:shadow-md"
                        :class="{
                            'border-emerald-200 bg-emerald-50/40': isVerified,
                            'border-amber-200 bg-amber-50/30': verificationStatus === 'pending',
                            'border-slate-200': !isVerified && verificationStatus !== 'pending',
                        }"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="{
                                'bg-emerald-100 text-emerald-600': isVerified,
                                'bg-amber-100 text-amber-600': verificationStatus === 'pending',
                                'bg-slate-100 text-slate-500': !isVerified && verificationStatus !== 'pending',
                            }"
                        >
                            <BadgeCheck v-if="isVerified" class="h-5 w-5" />
                            <Clock v-else-if="verificationStatus === 'pending'" class="h-5 w-5" />
                            <ShieldAlert v-else class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-400 uppercase">Tutor Verification</p>
                            <p
                                class="mt-0.5 text-sm font-bold"
                                :class="{
                                    'text-emerald-700': isVerified,
                                    'text-amber-700': verificationStatus === 'pending',
                                    'text-slate-700': !isVerified && verificationStatus !== 'pending',
                                }"
                            >
                                {{ verificationLabel }}
                            </p>
                        </div>
                    </Link>

                    <!-- Verification Fee Card -->
                    <Link
                        v-if="verificationFeeInvoice"
                        href="/tutor/finance/invoices"
                        class="group flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50/40 p-4 shadow-sm transition hover:shadow-md"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                            <FileText class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-amber-500 uppercase">Verification Fee</p>
                            <p class="mt-0.5 text-sm font-bold text-amber-700">
                                {{ verificationFeeInvoice.currency }}
                                {{ verificationFeeInvoice.amount }}
                                <span class="ml-1 text-xs font-semibold capitalize">
                                    — {{ verificationFeeInvoiceStatus }}
                                </span>
                            </p>
                        </div>
                    </Link>

                    <!-- Unverified hint (when no invoice yet & not verified) -->
                    <Link
                        v-else-if="!isVerified"
                        href="/tutor/profile"
                        class="group flex items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50/40 p-4 shadow-sm transition hover:shadow-md"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-500">
                            <FileText class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-blue-400 uppercase">Verification Fee</p>
                            <p class="mt-0.5 text-sm font-bold text-blue-700">BDT 200</p>
                            <p class="text-xs text-blue-500">One-time verification fee</p>
                        </div>
                    </Link>

                    <!-- Payments Card -->
                    <Link
                        href="/tutor/finance/invoices"
                        class="group flex items-center gap-4 rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
                        :class="pendingInvoiceCount > 0 ? 'border-rose-200 bg-rose-50/40' : 'border-border bg-card'"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="pendingInvoiceCount > 0 ? 'bg-rose-100 text-rose-600' : 'bg-blue-50 text-blue-500'"
                        >
                            <Wallet class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-400 uppercase">Fees & Payments</p>
                            <p
                                class="mt-0.5 text-sm font-bold"
                                :class="pendingInvoiceCount > 0 ? 'text-rose-700' : 'text-slate-600'"
                            >
                                <span v-if="pendingInvoiceCount > 0">
                                    {{ pendingInvoiceCount }} pending invoice{{ pendingInvoiceCount > 1 ? 's' : '' }}
                                </span>
                                <span v-else>No pending invoices</span>
                            </p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Quick Actions -->
            <div>
                <h2 class="mb-3 text-sm font-bold tracking-wide text-slate-500 uppercase">
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
    </TutorLayout>
</template>
