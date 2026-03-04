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
} from 'lucide-vue-next';
import NoticeCarousel from '@/components/NoticeCarousel.vue';
import TutorLayout from '@/layouts/TutorLayout.vue';

interface Notice {
    id: number;
    title: string;
    body: string;
    published_at: string;
    expires_at: string | null;
}

defineProps<{
    notices: Notice[];
}>();

const breadcrumbs = [{ title: 'Tutor Dashboard', href: '/tutor/dashboard' }];
</script>

<template>
    <Head title="Tutor Dashboard" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-4 sm:p-6 lg:p-8">
            <!-- Notices Carousel -->
            <NoticeCarousel :notices="notices" />

            <!-- Hero Section -->
            <div
                class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 p-8 text-white shadow-2xl shadow-blue-900/20 sm:p-10"
            >
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.2),transparent_50%)] opacity-80"
                ></div>
                <div
                    class="pointer-events-none absolute -bottom-20 -right-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"
                ></div>

                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-wider text-blue-200">
                        Welcome back
                    </p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">
                        Tutor Dashboard
                    </h1>
                    <p class="mt-3 max-w-xl text-base text-blue-100/90 sm:text-lg">
                        Manage your applications, update your profile, and grow your tutoring career.
                    </p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    href="/jobs"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-500/10 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/30 transition-transform duration-300 group-hover:scale-110">
                        <Search class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Browse Jobs
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Discover new tutoring opportunities matching your skills.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-blue-600" />
                </Link>

                <Link
                    href="/tutor/job-applications"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-500/10 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/30 transition-transform duration-300 group-hover:scale-110">
                        <Briefcase class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Application Pipeline
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Track applied, shortlisted, and appointed positions.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-emerald-600" />
                </Link>

                <Link
                    href="/tutor/profile"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-violet-200 hover:shadow-xl hover:shadow-violet-500/10 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 text-white shadow-lg shadow-violet-500/30 transition-transform duration-300 group-hover:scale-110">
                        <User class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        My Profile
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Update your bio, education, and teaching preferences.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-violet-600" />
                </Link>

                <Link
                    href="/tutor/finance/invoices"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-xl hover:shadow-amber-500/10 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-lg shadow-amber-500/30 transition-transform duration-300 group-hover:scale-110">
                        <Wallet class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Fees & Invoices
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        View invoices, payment history, and refund requests.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-amber-600" />
                </Link>

                <Link
                    href="/tutor/verification"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-xl hover:shadow-cyan-500/10 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 text-white shadow-lg shadow-cyan-500/30 transition-transform duration-300 group-hover:scale-110">
                        <FileText class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Verification
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Get verified to build trust with guardians.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-cyan-600" />
                </Link>

                <Link
                    href="/tutor/notifications"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-rose-200 hover:shadow-xl hover:shadow-rose-500/10 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 text-white shadow-lg shadow-rose-500/30 transition-transform duration-300 group-hover:scale-110">
                        <Bell class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                        Notifications
                    </h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Stay updated on opportunities and status changes.
                    </p>
                    <ArrowRight class="mt-4 h-5 w-5 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-rose-600" />
                </Link>
            </div>
        </div>
    </TutorLayout>
</template>
