<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Bell,
    Briefcase,
    Check,
    FileText,
    PlusCircle,
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

const props = defineProps<{
    notices: Notice[];
    jobStats: {
        pending: number;
        live: number;
        confirmed: number;
        cancelled: number;
        closed: number;
    };
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
</script>

<template>
    <Head title="Guardian Panel" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <DashboardWelcome />

            <NoticeCarousel
                v-if="notices.length > 0"
                :notices="notices"
            />

            <div>
                <h2
                    class="mb-4 text-base font-semibold text-card-foreground"
                >
                    Job Overview
                </h2>
                <div
                    class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"
                >
                    <StatCard
                        v-for="card in statCards"
                        :key="card.label"
                        v-bind="card"
                    />
                </div>
            </div>

            <div>
                <h2
                    class="mb-4 text-base font-semibold text-card-foreground"
                >
                    Quick Actions
                </h2>
                <div
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
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
