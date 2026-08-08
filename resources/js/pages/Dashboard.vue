<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Bell,
    Briefcase,
    FileText,
    GraduationCap,
    Search,
    Settings,
    User,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';
import ActionCard from '@/components/ActionCard.vue';
import DashboardWelcome from '@/components/DashboardWelcome.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import type { ActionCardConfig } from '@/types/dashboard';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
];

const page = usePage();
const user = computed(() => page.props.auth?.user);

const roleActions = computed<ActionCardConfig[]>(() => {
    const role = user.value?.role;

    if (role === 'admin') {
        return [
            {
                title: 'Manage Users',
                description: 'Manage tutors, guardians, and staff accounts.',
                href: '/admin/users',
                icon: User,
                color: 'blue',
            },
            {
                title: 'Manage Jobs',
                description: 'Review and manage all job listings.',
                href: '/admin/jobs',
                icon: Briefcase,
                color: 'emerald',
            },
            {
                title: 'Verifications',
                description: 'Approve identity and qualification documents.',
                href: '/admin/verifications',
                icon: FileText,
                color: 'violet',
            },
            {
                title: 'Finance',
                description: 'Invoices, payments, and refund requests.',
                href: '/admin/finance/invoices',
                icon: Wallet,
                color: 'amber',
            },
            {
                title: 'Tutors',
                description: 'Browse and manage tutor profiles.',
                href: '/admin/tutors',
                icon: GraduationCap,
                color: 'cyan',
            },
            {
                title: 'Settings',
                description: 'Site configuration and system settings.',
                href: '/admin/settings',
                icon: Settings,
                color: 'slate',
            },
        ];
    }

    if (role === 'tutor') {
        return [
            {
                title: 'Browse Jobs',
                description: 'Discover new tutoring opportunities.',
                href: '/jobs',
                icon: Search,
                color: 'blue',
            },
            {
                title: 'My Applications',
                description: 'Track your job applications.',
                href: '/tutor/job-applications',
                icon: Briefcase,
                color: 'emerald',
            },
            {
                title: 'My Profile',
                description: 'Update your bio and preferences.',
                href: '/tutor/profile',
                icon: User,
                color: 'violet',
            },
            {
                title: 'Fees & Invoices',
                description: 'View payments and invoices.',
                href: '/tutor/finance/invoices',
                icon: Wallet,
                color: 'amber',
            },
            {
                title: 'Verification',
                description: 'Get verified to build trust.',
                href: '/tutor/verification',
                icon: FileText,
                color: 'cyan',
            },
            {
                title: 'Notifications',
                description: 'Stay updated on status changes.',
                href: '/tutor/notifications',
                icon: Bell,
                color: 'rose',
            },
        ];
    }

    return [
        {
            title: 'Post a Job',
            description: 'Create a new tutoring requirement.',
            href: '/guardian/jobs/create',
            icon: Briefcase,
            color: 'blue',
        },
        {
            title: 'My Jobs',
            description: 'Manage your job listings.',
            href: '/guardian/jobs',
            icon: Search,
            color: 'emerald',
        },
        {
            title: 'My Profile',
            description: 'Update your contact info.',
            href: '/guardian/profile',
            icon: User,
            color: 'violet',
        },
        {
            title: 'Payments',
            description: 'View invoices and manage payments.',
            href: '/guardian/finance/invoices',
            icon: Wallet,
            color: 'amber',
        },
        {
            title: 'Verification',
            description: 'Verify your account.',
            href: '/guardian/verification',
            icon: FileText,
            color: 'cyan',
        },
        {
            title: 'Notifications',
            description: 'Monitor application updates.',
            href: '/guardian/notifications',
            icon: Bell,
            color: 'rose',
        },
    ];
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <DashboardWelcome />

            <div>
                <h2 class="mb-4 text-base font-semibold text-card-foreground">
                    Quick Actions
                </h2>
                <div
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <ActionCard
                        v-for="action in roleActions"
                        :key="action.title"
                        v-bind="action"
                    />
                </div>
            </div>

            <div
                class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm"
            >
                <h2 class="text-base font-semibold text-card-foreground">
                    Getting Started
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Use the sidebar or quick actions above to navigate the
                    platform.
                </p>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <Link
                        v-if="user?.role === 'tutor'"
                        href="/jobs"
                        class="flex items-start gap-4 rounded-xl border border-border/60 bg-muted/30 p-4 transition-all hover:border-primary/30 hover:bg-primary/5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <Search class="h-5 w-5" />
                        </div>
                        <div>
                            <h3
                                class="text-sm font-semibold text-card-foreground"
                            >
                                Find Your First Tutoring Job
                            </h3>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Browse available positions and apply to the ones
                                that match your skills.
                            </p>
                        </div>
                    </Link>
                    <Link
                        v-if="user?.role === 'guardian'"
                        href="/guardian/jobs/create"
                        class="flex items-start gap-4 rounded-xl border border-border/60 bg-muted/30 p-4 transition-all hover:border-primary/30 hover:bg-primary/5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <Briefcase class="h-5 w-5" />
                        </div>
                        <div>
                            <h3
                                class="text-sm font-semibold text-card-foreground"
                            >
                                Post Your First Job
                            </h3>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Describe your tutoring requirements and start
                                receiving applications.
                            </p>
                        </div>
                    </Link>
                    <Link
                        v-if="user?.role === 'admin'"
                        href="/admin/tutors"
                        class="flex items-start gap-4 rounded-xl border border-border/60 bg-muted/30 p-4 transition-all hover:border-primary/30 hover:bg-primary/5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <GraduationCap class="h-5 w-5" />
                        </div>
                        <div>
                            <h3
                                class="text-sm font-semibold text-card-foreground"
                            >
                                Review New Registrations
                            </h3>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Approve new tutors and guardians who have signed
                                up recently.
                            </p>
                        </div>
                    </Link>
                    <Link
                        href="/profile"
                        class="flex items-start gap-4 rounded-xl border border-border/60 bg-muted/30 p-4 transition-all hover:border-primary/30 hover:bg-primary/5"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <Settings class="h-5 w-5" />
                        </div>
                        <div>
                            <h3
                                class="text-sm font-semibold text-card-foreground"
                            >
                                Complete Your Profile
                            </h3>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Make sure your profile is complete for the best
                                experience.
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
