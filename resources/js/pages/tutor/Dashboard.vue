<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Bell,
    Briefcase,
    ClipboardCheck,
    FileText,
    Search,
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

const props = defineProps<{
    notices: Notice[];
    applicationStats: {
        applied: number;
        shortlisted: number;
        appointed: number;
        confirmed: number;
        cancelled: number;
    };
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
</script>

<template>
    <Head title="Tutor Panel" />

    <TutorLayout :breadcrumbs="breadcrumbs">
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
                    Application Overview
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
    </TutorLayout>
</template>
