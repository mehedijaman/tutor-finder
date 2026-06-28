<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Briefcase,
    User,
    Bell,
    FileText,
    Search,
    Wallet,
    ArrowRight,
    ClipboardCheck,
    UserCheck,
    BadgeCheck,
    X,
} from 'lucide-vue-next';
import { computed } from 'vue';
import NoticeCarousel from '@/components/NoticeCarousel.vue';
import TutorLayout from '@/layouts/TutorLayout.vue';

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

const applicationStatCards = computed(() => [
    {
        label: 'Applied',
        value: props.applicationStats.applied,
        href: '/tutor/job-applications',
        hint: 'Open applications',
        icon: Briefcase,
        cardClass: 'hover:border-slate-300 hover:shadow-lg',
        labelClass: 'text-slate-500',
        valueClass: 'text-slate-900',
        iconWrapClass: 'bg-slate-100 ring-slate-200/70',
        iconClass: 'text-slate-600',
        footerClass: 'text-slate-500',
    },
    {
        label: 'Shortlisted',
        value: props.applicationStats.shortlisted,
        href: '/tutor/job-applications/shortlisted',
        hint: 'Review prospects',
        icon: ClipboardCheck,
        cardClass: 'hover:border-blue-200 hover:shadow-lg',
        labelClass: 'text-blue-600',
        valueClass: 'text-blue-600',
        iconWrapClass: 'bg-blue-50 ring-blue-200/70',
        iconClass: 'text-blue-600',
        footerClass: 'text-blue-600/80',
    },
    {
        label: 'Appointed',
        value: props.applicationStats.appointed,
        href: '/tutor/job-applications/appointed',
        hint: 'Pending final steps',
        icon: UserCheck,
        cardClass: 'hover:border-amber-200 hover:shadow-lg',
        labelClass: 'text-amber-700',
        valueClass: 'text-amber-700',
        iconWrapClass: 'bg-amber-50 ring-amber-200/70',
        iconClass: 'text-amber-700',
        footerClass: 'text-amber-700/80',
    },
    {
        label: 'Confirmed',
        value: props.applicationStats.confirmed,
        href: '/tutor/job-applications/confirmed',
        hint: 'Successful matches',
        icon: BadgeCheck,
        cardClass: 'hover:border-emerald-200 hover:shadow-lg',
        labelClass: 'text-emerald-600',
        valueClass: 'text-emerald-600',
        iconWrapClass: 'bg-emerald-50 ring-emerald-200/70',
        iconClass: 'text-emerald-600',
        footerClass: 'text-emerald-700/80',
    },
    {
        label: 'Cancelled',
        value: props.applicationStats.cancelled,
        href: '/tutor/job-applications/cancelled',
        hint: 'Cancelled requests',
        icon: X,
        cardClass: 'hover:border-rose-200 hover:shadow-lg',
        labelClass: 'text-rose-600',
        valueClass: 'text-rose-600',
        iconWrapClass: 'bg-rose-50 ring-rose-200/70',
        iconClass: 'text-rose-600',
        footerClass: 'text-rose-700/80',
    },
]);

function formatStatCount(value: number): string {
    return String(value).padStart(2, '0');
}

const quickActionCards = [
    {
        title: 'Browse Jobs',
        description:
            'Discover new tutoring opportunities matching your skills.',
        href: '/jobs',
        icon: Search,
        cardClass:
            'hover:border-blue-200 hover:shadow-xl hover:shadow-blue-500/10 dark:border-slate-800 dark:bg-slate-900',
        iconGradientClass: 'from-blue-500 to-blue-600',
        iconShadowClass: 'shadow-blue-500/30',
        arrowHoverClass: 'group-hover:text-blue-600',
    },
    {
        title: 'Application Pipeline',
        description: 'Track applied, shortlisted, and appointed positions.',
        href: '/tutor/job-applications',
        icon: Briefcase,
        cardClass:
            'hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-500/10 dark:border-slate-800 dark:bg-slate-900',
        iconGradientClass: 'from-emerald-500 to-emerald-600',
        iconShadowClass: 'shadow-emerald-500/30',
        arrowHoverClass: 'group-hover:text-emerald-600',
    },
    {
        title: 'My Profile',
        description: 'Update your bio, education, and teaching preferences.',
        href: '/tutor/profile',
        icon: User,
        cardClass:
            'hover:border-violet-200 hover:shadow-xl hover:shadow-violet-500/10 dark:border-slate-800 dark:bg-slate-900',
        iconGradientClass: 'from-violet-500 to-violet-600',
        iconShadowClass: 'shadow-violet-500/30',
        arrowHoverClass: 'group-hover:text-violet-600',
    },
    {
        title: 'Fees & Invoices',
        description: 'View invoices, payment history, and refund requests.',
        href: '/tutor/finance/invoices',
        icon: Wallet,
        cardClass:
            'hover:border-amber-200 hover:shadow-xl hover:shadow-amber-500/10 dark:border-slate-800 dark:bg-slate-900',
        iconGradientClass: 'from-amber-500 to-amber-600',
        iconShadowClass: 'shadow-amber-500/30',
        arrowHoverClass: 'group-hover:text-amber-600',
    },
    {
        title: 'Verification',
        description: 'Get verified to build trust with guardians.',
        href: '/tutor/verification',
        icon: FileText,
        cardClass:
            'hover:border-cyan-200 hover:shadow-xl hover:shadow-cyan-500/10 dark:border-slate-800 dark:bg-slate-900',
        iconGradientClass: 'from-cyan-500 to-cyan-600',
        iconShadowClass: 'shadow-cyan-500/30',
        arrowHoverClass: 'group-hover:text-cyan-600',
    },
    {
        title: 'Notifications',
        description: 'Stay updated on opportunities and status changes.',
        href: '/tutor/notifications',
        icon: Bell,
        cardClass:
            'hover:border-rose-200 hover:shadow-xl hover:shadow-rose-500/10 dark:border-slate-800 dark:bg-slate-900',
        iconGradientClass: 'from-rose-500 to-rose-600',
        iconShadowClass: 'shadow-rose-500/30',
        arrowHoverClass: 'group-hover:text-rose-600',
    },
];
</script>

<template>
    <Head title="Tutor Panel" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4 sm:p-6 lg:p-8">
            <!-- Application Stats -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <Link
                    v-for="card in applicationStatCards"
                    :key="card.label"
                    :href="card.href"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1"
                    :class="card.cardClass"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-semibold tracking-[0.16em] uppercase"
                                :class="card.labelClass"
                            >
                                {{ card.label }}
                            </p>
                            <p
                                class="mt-3 text-4xl leading-none font-bold"
                                :class="card.valueClass"
                            >
                                {{ formatStatCount(card.value) }}
                            </p>
                        </div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl ring-1"
                            :class="card.iconWrapClass"
                        >
                            <component
                                :is="card.icon"
                                class="h-5 w-5"
                                :class="card.iconClass"
                            />
                        </div>
                    </div>
                    <div
                        class="mt-5 flex items-center justify-between text-xs font-medium"
                        :class="card.footerClass"
                    >
                        <span>{{ card.hint }}</span>
                        <ArrowRight
                            class="h-4 w-4 transition-transform group-hover:translate-x-1"
                        />
                    </div>
                </Link>
            </div>

            <!-- Notices Carousel -->
            <NoticeCarousel :notices="notices" />

            <!-- Quick Actions -->
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="action in quickActionCards"
                    :key="action.title"
                    :href="action.href"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1"
                    :class="action.cardClass"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-linear-to-br text-white shadow-lg transition-transform duration-300 group-hover:scale-110"
                        :class="[
                            action.iconGradientClass,
                            action.iconShadowClass,
                        ]"
                    >
                        <component :is="action.icon" class="h-6 w-6" />
                    </div>
                    <h3
                        class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white"
                    >
                        {{ action.title }}
                    </h3>
                    <p
                        class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400"
                    >
                        {{ action.description }}
                    </p>
                    <ArrowRight
                        class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1"
                        :class="action.arrowHoverClass"
                    />
                </Link>
            </div>
        </div>
    </TutorLayout>
</template>
