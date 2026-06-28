<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    PlusCircle,
    Briefcase,
    User,
    Bell,
    FileText,
    Wallet,
    ArrowRight,
    Check,
    X,
} from 'lucide-vue-next';
import { computed } from 'vue';
import NoticeCarousel from '@/components/NoticeCarousel.vue';
import GuardianLayout from '@/layouts/GuardianLayout.vue';

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

const jobStatCards = computed(() => [
    {
        label: 'Pending',
        value: props.jobStats.pending,
        href: '/guardian/jobs/pending',
        hint: 'Awaiting review',
        icon: FileText,
        cardClass: 'hover:border-amber-200 hover:shadow-lg',
        labelClass: 'text-amber-700',
        valueClass: 'text-amber-700',
        iconWrapClass: 'bg-amber-50 ring-amber-200/70',
        iconClass: 'text-amber-700',
        footerClass: 'text-amber-700/80',
    },
    {
        label: 'Live',
        value: props.jobStats.live,
        href: '/guardian/jobs/live',
        hint: 'Open for applications',
        icon: Briefcase,
        cardClass: 'hover:border-blue-200 hover:shadow-lg',
        labelClass: 'text-blue-600',
        valueClass: 'text-blue-600',
        iconWrapClass: 'bg-blue-50 ring-blue-200/70',
        iconClass: 'text-blue-600',
        footerClass: 'text-blue-700/80',
    },
    {
        label: 'Confirmed',
        value: props.jobStats.confirmed,
        href: '/guardian/jobs/confirmed',
        hint: 'Successful hires',
        icon: Check,
        cardClass: 'hover:border-emerald-200 hover:shadow-lg',
        labelClass: 'text-emerald-600',
        valueClass: 'text-emerald-600',
        iconWrapClass: 'bg-emerald-50 ring-emerald-200/70',
        iconClass: 'text-emerald-600',
        footerClass: 'text-emerald-700/80',
    },
    {
        label: 'Cancelled',
        value: props.jobStats.cancelled,
        href: '/guardian/jobs/cancelled',
        hint: 'Closed by guardian',
        icon: X,
        cardClass: 'hover:border-rose-200 hover:shadow-lg',
        labelClass: 'text-rose-600',
        valueClass: 'text-rose-600',
        iconWrapClass: 'bg-rose-50 ring-rose-200/70',
        iconClass: 'text-rose-600',
        footerClass: 'text-rose-700/80',
    },
    {
        label: 'Closed',
        value: props.jobStats.closed,
        href: '/guardian/jobs/closed',
        hint: 'Archived jobs',
        icon: Briefcase,
        cardClass: 'hover:border-slate-300 hover:shadow-lg',
        labelClass: 'text-slate-600',
        valueClass: 'text-slate-700',
        iconWrapClass: 'bg-slate-100 ring-slate-200/70',
        iconClass: 'text-slate-700',
        footerClass: 'text-slate-600',
    },
]);

function formatStatCount(value: number): string {
    return String(value).padStart(2, '0');
}

const quickActionCards = [
    {
        title: 'Post New Job',
        description: 'Create a new tutoring requirement in minutes.',
        href: '/guardian/jobs/create',
        icon: PlusCircle,
        cardClass:
            'border-2 border-dashed border-slate-300 bg-slate-50/50 hover:border-blue-400 hover:bg-blue-50/50 hover:shadow-xl hover:shadow-blue-500/10 dark:border-slate-700 dark:bg-slate-900/50 dark:hover:border-blue-500 dark:hover:bg-blue-950/20',
        iconGradientClass: 'from-blue-500 to-blue-600',
        iconShadowClass: 'shadow-blue-500/30',
        arrowHoverClass: 'group-hover:text-blue-600',
    },
    {
        title: 'Hiring Pipeline',
        description: 'Review live, confirmed, and closed job activity.',
        href: '/guardian/jobs',
        icon: Briefcase,
        cardClass:
            'hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-500/10 dark:border-slate-800 dark:bg-slate-900',
        iconGradientClass: 'from-emerald-500 to-emerald-600',
        iconShadowClass: 'shadow-emerald-500/30',
        arrowHoverClass: 'group-hover:text-emerald-600',
    },
    {
        title: 'My Profile',
        description: 'Update your contact info and preferences.',
        href: '/guardian/profile',
        icon: User,
        cardClass:
            'hover:border-violet-200 hover:shadow-xl hover:shadow-violet-500/10 dark:border-slate-800 dark:bg-slate-900',
        iconGradientClass: 'from-violet-500 to-violet-600',
        iconShadowClass: 'shadow-violet-500/30',
        arrowHoverClass: 'group-hover:text-violet-600',
    },
    {
        title: 'Payments & Escrow',
        description: 'View invoices and manage secure payments.',
        href: '/guardian/finance/invoices',
        icon: Wallet,
        cardClass:
            'hover:border-amber-200 hover:shadow-xl hover:shadow-amber-500/10 dark:border-slate-800 dark:bg-slate-900',
        iconGradientClass: 'from-amber-500 to-amber-600',
        iconShadowClass: 'shadow-amber-500/30',
        arrowHoverClass: 'group-hover:text-amber-600',
    },
    {
        title: 'Verification',
        description: 'Verify your account for enhanced trust.',
        href: '/guardian/verification',
        icon: FileText,
        cardClass:
            'hover:border-cyan-200 hover:shadow-xl hover:shadow-cyan-500/10 dark:border-slate-800 dark:bg-slate-900',
        iconGradientClass: 'from-cyan-500 to-cyan-600',
        iconShadowClass: 'shadow-cyan-500/30',
        arrowHoverClass: 'group-hover:text-cyan-600',
    },
    {
        title: 'Notifications',
        description: 'Monitor application updates and hiring milestones.',
        href: '/guardian/notifications',
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
    <Head title="Guardian Panel" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4 sm:p-6 lg:p-8">
            <!-- Job Stats -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <Link
                    v-for="card in jobStatCards"
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
    </GuardianLayout>
</template>
